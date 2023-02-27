<?php

/** @noinspection ALL */
/** @noinspection ALL */

/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-06 16:14:15
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Template.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use function htmlspecialchars;
use function is_string;
use function substr;

// 应用层级, 用于模板的解析, 以及模板的渲染

class Template
{
    private static $arguments = array();
    private static $regexs = array(
        'foreach' => '/@foreach[\t\s]*\([\t\s]*(?:(?:(\$\w+(?:\[(?:\$*\w+|(?:"(?:[^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'(?:[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'))\])?)[\t\s]+as[\t\s]+(\$\w+))(?:[\t\s]*=>[\t\s]*(\g<1>))?[\t\s]*\))\R((?:\g<0>|(?!(@foreach|@endforeach))[\s\S]*?)+\R)?[\t\s]*@endforeach/',
        'for' => '/@for[\s\t]*\(([\s\S]*?)?\;([\s\S]*?)\;([\s\S]*?)\)[\s\t]*((?:\g<0>|(?!(@for|@endfor))[\s\S]*?)+\R)[\t\s]*@endfor/',
        'while' => '/@while[\s\t]*\(([\s\S]*?)\)\R+((?:\g<0>|(?!(@while|@endwhile))[\s\S]*?)+\R)?[\t\s]*@endwhile/',
        'if' => '/@?(?:if|elseif)[\s\t]*\(([\s\S]*?)\)\R([\s\S]*?)\R[\s\t]*(@else([\s\S]*?)\R)?[\s\t]*@endif/',
        'embed' => '/@embed[\s]*\((.*){1}\)[\s]*@endembed/'
    );

    public static function define(string $key, mixed $value): void
    {
        self::$arguments = array_merge(self::$arguments, [$key => $value]);
    }

    private static function executeForeach(array $arguments, array $tempArgv = array()): string
    {
        foreach (array_merge(self::$arguments, $tempArgv) as $executeIfForeachKey => $executeIfForeachValue) {
            $$executeIfForeachKey = $executeIfForeachValue;
        }
        $originContent = $arguments[0];
        $templateFrammentArrayName = $arguments[1];
        $templateFrammentKeyName = $arguments[2];
        $templateFrammentItemName = $arguments[3];
        $templateFrammentContent = $arguments[4];
        if ($templateFrammentItemName === '') $templateFrammentItemName = $templateFrammentKeyName;
        foreach (self::$arguments as $applyForeachAssignmentKey => $applyForeachAssignmentValue)
            $$applyForeachAssignmentKey = $applyForeachAssignmentValue;
        $templateFrammentHtml = '';
        $templateFrammentArray = eval('return ' . $templateFrammentArrayName . ';');
        $templateFrammentIndex = 0;
        foreach ($templateFrammentArray as $templateFrammentKey => $templateFrammentValue) {
            $templateFrammentHtml .= self::apply($templateFrammentContent, [
                substr($templateFrammentKeyName, 1) => $templateFrammentKey,
                substr($templateFrammentItemName, 1) => $templateFrammentValue,
            ]);
            $templateFrammentIndex++;
        }
        return $templateFrammentHtml;
    }

    public static function apply(string $applyTemplateText, array $tempArguments = array()): string
    {
        //定义变量
        foreach (array_merge(self::$arguments, $tempArguments) as $applyForeachAssignmentKey => $applyForeachAssignmentValue)
            $$applyForeachAssignmentKey = $applyForeachAssignmentValue;
        $applyLableCircuitStack = array();
        $applyTemplateTextPoint = 0;
        while ($applyTemplateTextPoint < strlen($applyTemplateText)) {
            $applyLableCircuitStackCount = count($applyLableCircuitStack);
            $applyTemplateTextPointChar = substr($applyTemplateText, $applyTemplateTextPoint++, 1);
            if ($applyTemplateTextPointChar === '@') {
                foreach (self::$regexs as $applyForeachAssignmentKey => $applyForeachAssignmentValue) {
                    if (strpos($applyTemplateText, $applyForeachAssignmentKey, $applyTemplateTextPoint) === $applyTemplateTextPoint) {
                        if ($applyLableCircuitStackCount === 0) $startIndex = $applyTemplateTextPoint - 1;
                        $applyLableName = $applyForeachAssignmentKey;
                        $applyLableCircuitStack[] = $applyForeachAssignmentKey;
                        break;
                    } elseif (end($applyLableCircuitStack) !== false && strpos($applyTemplateText, 'end' . end($applyLableCircuitStack), $applyTemplateTextPoint) === $applyTemplateTextPoint) {
                        $applyLableName = array_pop($applyLableCircuitStack);
                        break;
                    }
                }
            }

            if ($applyLableCircuitStackCount === 1 && count($applyLableCircuitStack) === 0) {
                $applyTemplateFragment = substr($applyTemplateText, $startIndex, $applyTemplateTextPoint - $startIndex + strlen('end' . $applyLableName));
                $applyTemplateFragmentResult = self::execute($applyTemplateFragment, $applyLableName, get_defined_vars());
                if ($applyTemplateFragmentResult !== false) {
                    $applyTemplateText = str_replace($applyTemplateFragment, $applyTemplateFragmentResult, $applyTemplateText);
                    $applyTemplateTextPoint += strlen($applyTemplateFragmentResult) - strlen($applyTemplateFragment);
                }
            }
        }

        //处理花括号
        $applyBraceMatchCount = preg_match_all('/@?\{\{((?:[^\}\\\\]*(?:\\\\.[^}\\\\]*)*))\}\}/', $applyTemplateText, $applyBraceMatchResult);
        for ($applyBracecExecIndex = 0; $applyBracecExecIndex < $applyBraceMatchCount; $applyBracecExecIndex++) {
            if (strpos($applyBraceMatchResult[0][$applyBracecExecIndex], '@') === 0) {
                $applyTemplateText = str_replace($applyBraceMatchResult[0][$applyBracecExecIndex], substr($applyBraceMatchResult[0][$applyBracecExecIndex], 1), $applyTemplateText);
            } else {
                $applyBracecExecResult = eval("return {$applyBraceMatchResult[1][$applyBracecExecIndex]};");
                if ($applyBracecExecResult !== false)
                    if (is_string($applyBracecExecResult)) {
                        $applyBracecExecResult = htmlspecialchars($applyBracecExecResult);
                    }
                $applyTemplateText = str_replace($applyBraceMatchResult[0][$applyBracecExecIndex], $applyBracecExecResult, $applyTemplateText);
            }
        }

        //处理所有PHP语句
        $phpFrammentMatchCount = preg_match_all('/@php([\s\S]+?)@endphp/', $applyTemplateText, $phpFrammentMatchResult);
        for ($phpFrammentMatchIndex = 0; $phpFrammentMatchIndex < $phpFrammentMatchCount; $phpFrammentMatchIndex++) {
            ob_start();
            eval($phpFrammentMatchResult[1][$phpFrammentMatchIndex]);
            $output = ob_get_clean();
            $applyTemplateText = str_replace($phpFrammentMatchResult[0][$phpFrammentMatchIndex], $output, $applyTemplateText);
        }
        return $applyTemplateText;
    }

    private static function execute(string $content, string $tag, array $arguments): string|false
    {
        $matchc = preg_match(self::$regexs[$tag], $content, $matchs);
        if ($matchc > 0)
            return call_user_func([__CLASS__, 'execute' . ucfirst($tag)], $matchs, $arguments);
        return false;
    }

    private static function executeFor(array $executeForArguments, array $tempArgv = array()): string
    {
        foreach (array_merge(self::$arguments, $tempArgv) as $executeIfForeachKey => $executeIfForeachValue) {
            $$executeIfForeachKey = $executeIfForeachValue;
        }
        $executeForStart = $executeForArguments[1];
        $executeForCondition = $executeForArguments[2];
        $executeForEnd = $executeForArguments[3];
        $executeForFarmment = $executeForArguments[4];
        $templateFramment = '';
        eval("{$executeForStart};");
        while (eval("return {$executeForCondition};")) {
            $templateFramment .= self::apply($executeForFarmment, get_defined_vars());
            eval("{$executeForEnd};");
        }
        return str_replace($executeForArguments[0], $templateFramment, $executeForArguments[0]);
    }

    private static function executeWhile(array $arguments, array $tempArgv = array()): string
    {
        foreach (array_merge(self::$arguments, $tempArgv) as $executeIfForeachKey => $executeIfForeachValue) {
            $$executeIfForeachKey = $executeIfForeachValue;
        }
        $executeWhileFarmment = '';
        while (eval("return {$arguments[1]};")) {
            $executeWhileFarmment .= self::apply($arguments[2], get_defined_vars());
        }
        return $executeWhileFarmment;
    }

    private static function executeIf(array $arguments, array $tempArgv = array()): string
    {
        foreach (array_merge(self::$arguments, $tempArgv) as $executeIfForeachKey => $executeIfForeachValue) {
            $$executeIfForeachKey = $executeIfForeachValue;
        }
        $executeIfCondition = $arguments[1];
        $executeIfAccord = $arguments[2];
        $executeIfElse = $arguments[3] ?? null;

        if (eval("return {$executeIfCondition};")) {
            return self::apply($executeIfAccord);
        } elseif ($executeIfElse !== null) {
            return self::apply("@" . $executeIfElse . "\n@endif", get_defined_vars());
        } else {
            return '';
        }
    }

    private static function executeEmbed(array $arguments, array $tempArgv = array()): string
    {
        $executeEmbedTemplateFile = trim($arguments[1], '"\'');
        //        $executeEmbedTemplateContent
        return file_get_contents(TMP_PATH . FS . $executeEmbedTemplateFile . '.' . Config::get('http.template_extension'));
    }
}
