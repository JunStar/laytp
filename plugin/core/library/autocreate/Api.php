<?php

namespace plugin\core\library\autocreate;

use laytp\traits\Error;
use plugin\core\library\autocreate\Api\library\Builder;
use plugin\core\library\autocreate\Api\library\Extractor;
use laytp\library\DirFile;
use think\facade\Config;
use think\facade\Env;

class Api
{
    use Error;
    protected $template = 'index.html';
    protected $output = 'api.html';
    protected $force = true;

    /**
     * 执行生成程序
     * @param $title
     * @return bool
     * @throws \Exception
     */
    public function execute($title)
    {
//        $addon = $input->getOption('addon');
//        if($addon){
//            $addon_service = new Addons();
//            $addon_info = $addon_service->_info->getPluginInfo($addon);
//            $controllerDir = Env::get('root_path') . DS . 'addons' . DS . $addon . DS . $addon_info['api_module'] . DS;
//        }else{
        $controllerDir = app()->getRootPath() . DS . 'app' . DS . 'api' . DS . 'controller' . DS;
//        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir), \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $weighs = [];
        $k      = 0;
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath         = $file->getRealPath();
                $className        = $this->getClassFromFile($filePath);
                $classAnnotations = Extractor::getClassAnnotations($className);
                if (isset($classAnnotations['ApiInternal'])) {
                    continue;
                }
                $weigh                                      = isset($classAnnotations['ApiWeigh']) ? intval($classAnnotations['ApiWeigh'][0]) : $k;
                $weighs[$this->getClassFromFile($filePath)] = $weigh;
                $k++;
            }
        }
        uasort($weighs, function ($a, $b) {
            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        });

        $classes = array_flip($weighs);

        $builder           = new Builder($classes);
        $apiDir            = __DIR__ . DS . 'Api' . DS;
        $templateDir       = $apiDir . 'template' . DS;
        $templateFile      = $templateDir . $this->template;
        $var['plugin']     = '';
        $var['title']      = $title;
        $var['api_domain'] = Env::get('domain.api_domain');
        $content           = $builder->render($templateFile, $var);

        $outputDir  = app()->getRootPath() . DS . 'public' . DS;
        $outputFile = $outputDir . $this->output;
        DirFile::createDir(dirname($outputFile));
        if (!file_put_contents($outputFile, $content)) {
            $this->setError('Cannot save the content to ' . $outputFile);
            return false;
        }
        return true;
    }

    /**
     * get full qualified class name
     *
     * @param string $path_to_file
     * @return string
     * @author JBYRNE http://jarretbyrne.com/2015/06/197/
     */
    protected function getClassFromFile($path_to_file)
    {
        //Grab the contents of the file
        $contents = file_get_contents($path_to_file);

        //Start with a blank namespace and class
        $namespace = $class = "";

        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = $getting_class = false;

        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {

            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }

            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS) {
                $getting_class = true;
            }

            //While we're grabbing the namespace name...
            if ($getting_namespace === true) {

                //If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {

                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } else if ($token === ';') {

                    //If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;
                }
            }

            //While we're grabbing the class name...
            if ($getting_class === true) {

                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {

                    //Store the token's value as the class name
                    $class = $token[1];

                    //Got what we need, stope here
                    break;
                }
            }
        }

        //Build the fully-qualified class name and return it
        return $namespace ? $namespace . '\\' . $class : $class;
    }
}
