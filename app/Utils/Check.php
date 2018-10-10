<?php


namespace App\Utils;

class Check
{
    //
    public static function isEmailLegal($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    //常用邮箱
    public static function filterNormailEmail($email)
    {
        $mailsuffixs = array(
            '@hotmail.com',
            '@msn.com',
            '@yahoo.com',
            '@gmail.com',
            '@aim.com',
            '@aol.com',
            '@mail.com',
            '@walla.com',
            '@inbox.com',
            '@126.com',
            '@163.com',
            '@sina.com',
            '@21cn.com',
            '@sohu.com',
            '@yahoo.com.cn',
            '@tom.com',
            '@qq.com',
            '@etang.com',
            '@eyou.com',
            '@56.com',
            '@x.cn',
            '@chinaren.com',
            '@sogou.com',
            '@citiz.com'
        );

        foreach ($mailsuffixs as $value) {
            if (strpos($email, $value) > 0) {
                return true;
            }
        }
        return false;
    }
}
