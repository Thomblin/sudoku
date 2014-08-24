<?php

/**
 * Class Options is used to read all cli options
 */
class Options
{
    /**
     * @var string[]
     */
    private $arguments = array();
    /**
     * @var string
     */
    private $script;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var string[]
     */
    private $options = array();

    public function __construct($argv)
    {
        $this->arguments = $argv;

        if ( empty($argv) ) {
            throw new InvalidArgumentException('$argv is empty');
        }

        $this->script = array_shift($argv);

        if ( !empty($argv) ) {
            $this->filename = array_pop($argv);
        }

        foreach ( $argv as $parameter ) {

            if ( false !== strpos($parameter, '=') ) {
                list($name, $value) = explode('=', $parameter, 2);
            } else {
                $name  = $parameter;
                $value = null;
            }

            if ( preg_match('~^[-]+(.*)~', $name, $match) ) {
                $name = $match[1];
            }

            $this->options[$name] = $value;
        }
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @return string
     */
    public function issetFilename()
    {
        return null !== $this->filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $name
     */
    public function issetOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getOption($name)
    {
        return $this->options[$name];
    }
} 