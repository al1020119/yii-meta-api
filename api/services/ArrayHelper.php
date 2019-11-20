<?php

namespace api\services;


class ArrayHelper
{
    /*
    * 自定义多维数组排序
    *
    * @param array array 二维数组
    * @param rules array ['field1' => SORT_ASC, 'field2' => SORT_DESC, 'field3' => [SORT_ASC, SORT_STRING]]
    * 
    */
    public static function multisort(array &$array, array $rules)
    {

        $args = [];
        foreach ($rules as $field => $rule) {
            $args[] = array_column($array, $field);
            if (is_array($rule)) {
                static::merge($args, $rule);
            } else {
                $args[] = $rule;
            }
        }

        $args[] = &$array;

        call_user_func_array('array_multisort', $args);
    }

    // 往数组后衔接数组，适用于array_merge 和 + 都失效的情况
    public static function merge(array &$source, array $add)
    {
        foreach ($add as $value) {
            $source[] = $value;
        }
    }

    /**
     * 根据key字段分组
     * @param $array
     * @param $key
     * @param $merge bool 是否合并
     * @return array
     */
    public static function groupArrayByKey($array, $key, $merge = true)
    {
        $newArr = [];
        foreach ($array as $v){
            if (array_key_exists($key,$v)) {
                if ($merge) {
                    $newArr[$v[$key]][] = $v;
                } else {
                    $newArr[$v[$key]] = $v;
                }
            }
        }

        return $newArr;
    }

    /**
     * 多维数组的值 模糊搜索
     * @param array $array
     * @param $key
     * @return array|int|string
     */
    public static function arraySearch($key,$array)
    {
        if (empty($array)){
            return [];
        }

        foreach ($array as $k=>$v){
            if (is_string($v) && strstr($v,$key) != false){
                return $k;
            }elseif(is_array($v)){
                $v = implode(',',$v);
                if (strstr($v,$key) != false){
                    return $k;
                }
            }
        }
        return [];
    }

    /**
     * @param array $array
     * @param int $rules SORT_ASC or SORT_DESC
     * @param string $sortType key：以键排序 value：以值排序
     * @return array
     */
    public static function arrayKeySort(Array $array, $rules = SORT_ASC, $sortType = 'key')
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::arrayKeySort($value, $rules, $sortType);
            }
        }
        if ($sortType == 'key' && $rules == SORT_ASC) {
            ksort($array);
        } elseif ($sortType == 'key' && $rules == SORT_DESC) {
            krsort($array);
        } else {
            array_multisort($array, $rules);
        }
        return $array;
    }
}