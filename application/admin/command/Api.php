<?php

namespace app\admin\command;

use app\admin\command\Api\library\Builder;
use library\DirFile;
use think\facade\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\Env;

class Api extends Command
{

    protected function configure()
    {
        $this
            ->setName('api')
            ->addOption('url', 'u', Option::VALUE_OPTIONAL, 'default api url', '')
            ->addOption('addon', 'addon', Option::VALUE_OPTIONAL, 'addon name', '')
            ->addOption('module', 'm', Option::VALUE_OPTIONAL, 'module name(admin/index/api)', 'api')
            ->addOption('output', 'o', Option::VALUE_OPTIONAL, 'output index file name', 'api.html')
            ->addOption('template', 'e', Option::VALUE_OPTIONAL, '', 'index.html')
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'force override general file', false)
            ->addOption('title', 't', Option::VALUE_OPTIONAL, 'document title', 'Api文档')
            ->addOption('author', 'a', Option::VALUE_OPTIONAL, 'document author', '暂无')
            ->addOption('class', 'c', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'extend class', null)
            ->setDescription('Api');
    }

    protected function execute(Input $input, Output $output)
    {
        $addon = $input->getOption('addon');
        if($addon){
            $controllerDir = Env::get('root_path') . DS . 'addons' . DS . $addon . DS . 'api' . DS;
        }else{
            $controllerDir = Env::get('app_path') . DS . 'api' . DS . Config::get('url_controller_layer') . DS;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir), \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $classes = [];
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $classes[] = $this->get_class_from_file($filePath);
            }
        }

        $builder = new Builder($classes);
        $apiDir = __DIR__ . DS . 'Api' . DS;
        $template_dir = $apiDir . 'template' . DS;
        $template_file = $template_dir . $input->getOption('template');
        $var['title'] = $input->getOption('title');

        $content = $builder->render($template_file,$var);

        $output_dir = Env::get('root_path') . 'public' . DS;
        $output_file = $output_dir . $input->getOption('output');
        DirFile::createDir(dirname($output_file));
        if (!file_put_contents($output_file, $content)) {
            throw new Exception('Cannot save the content to ' . $output_file);
        }
        $output->info("Build Successed!");
    }

    /**
     * get full qualified class name
     *
     * @param string $path_to_file
     * @author JBYRNE http://jarretbyrne.com/2015/06/197/
     * @return string
     */
    protected function get_class_from_file($path_to_file)
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
