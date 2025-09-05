<?php

namespace Drupal\nor_publications\Service;

/**
 * Class Utils
 * @package Drupal\nor_publications\Service
 */
class Utils {

  public function cloneEntity(
    $src, $dst, 
    $src_module, $des_module,
    $src_url, $des_url,
    $src_template, $des_template,
    $src_class, $des_class,
    $src_lower_text, $des_lower_text,
    $src_upper_text, $des_upper_text,
    $src_package, $des_package,
    $ignore_files
  ) {
    $dir = opendir($src);
    @mkdir($dst);

    while ($file = readdir($dir)) {
      if (($file != '.') && ($file != '..') && ($file != '.git')) {
        if (is_dir($src . '/' . $file)) {
          $this->cloneEntity($src . '/' . $file, $dst . '/' . $file,
            $src_module, $des_module,
            $src_url, $des_url,
            $src_template, $des_template,
            $src_class, $des_class,
            $src_lower_text, $des_lower_text,
            $src_upper_text, $des_upper_text,
            $src_package, $des_package,
            $ignore_files);
        }
        else {
          if(!in_array($file, $ignore_files)) {
            $str = $file;
            $src_template_url = str_replace("_", "-", $src_template);
            $des_template_url = str_replace("_", "-", $des_template);
            $str = str_replace($src_template, $des_template, $str);
            $str = str_replace($src_template_url, $des_template_url, $str);
            $str = str_replace($src_module, $des_module, $str);
            $str = str_replace($src_url, $des_url, $str);
            $str = str_replace($src_class, $des_class, $str);
            copy($src . '/' . $file, $dst . '/' . $str);
            $this->replaceInFile($dst . '/' . $str, $src_template, $des_template);
            $this->replaceInFile($dst . '/' . $str, $src_template_url, $des_template_url);
            $this->replaceInFile($dst . '/' . $str, $src_module, $des_module);
            $this->replaceInFile($dst . '/' . $str, $src_url, $des_url);
            $this->replaceInFile($dst . '/' . $str, $src_class, $des_class);
            $this->replaceInFile($dst . '/' . $str, $src_lower_text, $des_lower_text);
            $this->replaceInFile($dst . '/' . $str, $src_upper_text, $des_upper_text);
            $this->replaceInFile($dst . '/' . $str, $src_package, $des_package);
          }
        }
      }
    }
    closedir($dir);
  }

  /**
   * @param $path
   * @param $src_str
   * @param $des_str
   */
  public function replaceInFile($path, $src_str, $des_str) {
    $content = file_get_contents($path);
    $content = str_replace($src_str, $des_str, $content);
    file_put_contents($path, $content);
  }
}
