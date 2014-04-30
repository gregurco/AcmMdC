<?php
/**
 * CConsole class
 *
 * PHP version 5
 *
 * @category Components
 * @package  Ext.console
 * @author   Evgeniy Marilev <jeka@5-soft.com>
 */
/**
 * CConsole is the class for working with system shell
 * and yii console
 *
 * @category Components
 * @package  Ext.console
 * @author   Evgeniy Marilev <jeka@5-soft.com>
 */
class CConsole extends CComponent
{
    /**
     * Success return code
     * @var integer
     */
    const RETURN_CODE_SUCCESS = 0;
    
    /**
     * Path to application's commands script
     * @var string
     */
    public $commandsPath = 'application.yiic';
    
    /**
     * Whether required interactive command input/output displaying
     * @var boolean
     */
    public $displayCommands = false;
    
    /**
     * Constructs console object
     * 
     * @return void
     */
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * Initializes console
     * 
     * @return void
     */
    public function init()
    {
    }
    
    /**
     * Executes shell command
     * 
     * @param string|array $shell          shell command string with params or shell array (array(command, arg1, arg2, ...))
     * @param boolean      $async          whether required to run shell command asynchronous
     * @param array        &$outputLines   cli output lines array
     * @param string|array $redirectOutput file to redirect output or array with files to redirect output
     * 
     * @return integer console command return code
     */
    public function exec($shell, $async = false, &$outputLines = null, $redirectOutput = '')
    {
        $result = '';
        $shell = $this->resolveCommandLine($shell, $async, $redirectOutput);
        $ret = self::RETURN_CODE_SUCCESS;
        if ($this->displayCommands) {
            echo $shell . "\n";
        }
        exec($shell, $outputLines, $ret);
        if ($this->displayCommands) {
            foreach ($outputLines as $line) {
                $result .= $line . "<br>";
            }
        }
        
        return $result;
    }
    
    /**
     * Executes external program and outputs raw output.
     * 
     * @param string|array $shell          shell command string with params or shell array (array(command, arg1, arg2, ...))
     * @param string|array $redirectOutput file to redirect output or array with files to redirect output
     * 
     * @return integer console command return code
     */
    public function passthru($shell, $redirectOutput = '')
    {
        $shell = $this->resolveCommandLine($shell, false, $redirectOutput);
        $ret = self::RETURN_CODE_SUCCESS;
        $ret = self::RETURN_CODE_SUCCESS;
        if (!$this->displayCommands) {
            ob_start();
        }
        passthru($shell, $ret);
        if (!$this->displayCommands) {
            ob_end_clean();
        }
        return $ret;
    }
    
    /**
     * Resolves resulted command line string
     * 
     * @param string|array $shell          shell command string with params or shell array (array(command, arg1, arg2, ...))
     * @param boolean      $async          whether required to run shell command asynchronous
     * @param string|array $redirectOutput file to redirect output or array with files to redirect output
     */
    protected function resolveCommandLine($shell, $async, $redirectOutput)
    {
        if (is_array($shell)) {
            $command = escapeshellcmd($shell[0]);
            unset($shell[0]);
            foreach ($shell as $param => $value) {
                if (is_string($param)) {
                    $command .= ' ' . escapeshellarg($param);
                }
                $command .= ' ' . $value;
            }
        } else if (is_string($shell)) {
            $command = $shell;
        } else {
            throw new CException('Invalid shell command format. It can be string or array');
        }
        
        if ($async) {
            $asyncOutputs = array('/dev/null', '&1');
            $redirectOutput = is_array($redirectOutput) ? $redirectOutput : (!empty($redirectOutput) ? array($redirectOutput) : array());
            $redirectOutput = array_merge($asyncOutputs, $redirectOutput);
        }
        
        if (is_array($redirectOutput)) {
            foreach ($redirectOutput as $i => $file) {
                $command .= ' ';
                if ($i > 0) {
                    $command .= ($i + 1);
                }
                $command .= '>' . $file;
            }
        }
        
        $command .= $async ? ' &' : '';
        return $command;
    }
    
    /**
     * Runs console command
     * 
     * @param string $command      console command name
     * @param array  $args         command arguments array
     * @param bool   $async        whether required to run console command asynchronous
     * @param array  &$outputLines console output lines array
     * @param string $executor     executor method name
     * 
     * @return integer console command return code
     */
    public function runCommand($command, $args, $async = false, &$outputLines = null, $executor = 'passthru')
    {
        $pathToYiic = $this->getYiiConsolePath();
        $shell = array(
            $pathToYiic,
            $command,
        );
        $shell = CMap::mergeArray($shell, $args);
        if (defined('YII_DEBUG') && YII_DEBUG) {
            echo 'Running console command: ' . CVarDumper::dumpAsString($shell);
        }
        switch ($executor) {
            case 'passthru':
                return $this->passthru($shell);
            case 'exec':
                return $this->exec($shell, $async, $outputLines);
            default:
                throw Exception('Unknown executor name: "' . $executor . '"');
        }
    }
    
    /**
     * Returns path to application's yii console
     * 
     * @return string path to yii console
     */
    protected function getYiiConsolePath()
    {
        return Yii::getPathOfAlias($this->commandsPath);
    }
}