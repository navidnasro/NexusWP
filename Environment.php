<?php

namespace jetstore;

enum Environment
{
    const PluginRoot = __DIR__;
    const PluginFile = __FILE__;
    const PublicFolder = __DIR__.'/public';
    const Storage  = __DIR__.'/storage';
    const Views  = __DIR__.'/app/views';
    const IMG = RootUrl.'/public/assets/img'; // path to images folder
    const CSS = RootUrl.'/public/assets/css'; // path to css folder
    const JS  = RootUrl.'/public/assets/js'; // path to js folder
    const TextDomain = 'jetstore'; // text domain name

    // database credentials
    const DbName = DB_NAME;
    const DbUser = DB_USER;
    const DbPassword = DB_PASSWORD;
    const DbHost = DB_HOST;
    const DbCharset = DB_CHARSET;
    const TablePreFix = 'jetstore_';
}
