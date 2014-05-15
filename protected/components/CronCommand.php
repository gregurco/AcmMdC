<?
set_time_limit(0);

class CronCommand extends CConsoleCommand
{
    public $dbConnection = '';
	private $console;
	

    public function run() {
		$this->console = new CConsole();
		
        /* Компилируем программы */
		while ($this->compile())
			sleep(5);
    }
	
    private function compile(){
        $model = Processing::model()->findAllByAttributes(array('status' => 1));
        foreach($model as $processing){
            Processing::model()->updateByPk($processing->id, array('status' => 2));
			
            $dir = '/var/www/AcmMdC/files/testing';
			file_put_contents($dir.'/'.$this->fileName($processing->compiler), $processing->file_text);

            if ($processing->compiler == 'FPC'){
                $compile_result = $this->console->exec('cd '.$dir.'; fpc '.$dir.'/1 2> log.txt');
            }elseif($processing->compiler == 'GCC'){
                $this->console->exec('cd '.$dir.'; gcc '.$dir.'/'.$this->fileName($processing->compiler).' -o '.$dir.'/1 2> '.$dir.'/log.txt');
            }elseif($processing->compiler == 'G++'){
                $this->console->exec('cd '.$dir.'; g++ '.$dir.'/'.$this->fileName($processing->compiler).' -o '.$dir.'/1 2> '.$dir.'/log.txt');
            }elseif($processing->compiler == 'Prolog'){
                $this->console->exec('cd '.$dir.'; swipl --goal=goal --stand_alone=true -o 1 -c 1.pl 2> '.$dir.'/log.txt');
            }elseif($processing->compiler == 'Java'){
                $this->console->exec('cd '.$dir.'; javac '.$dir.'/'.$this->fileName($processing->compiler).' 2> '.$dir.'/log.txt');
            }
			
			$compile_result = file_get_contents($dir.'/log.txt');
            $compile_result = nl2br($compile_result);

            if (($processing->compiler == 'Java' && file_exists($dir.'/Main.class')) || ($processing->compiler != 'Java' && file_exists($dir.'/1'))){
                /* Успешная компиляция */
                Processing::model()->updateByPk($processing->id, array('status' => 3,  'log_compile' => $compile_result));
				$this->test($processing);
            }else{
                /* Ошибка: Ошибка компиляции */
                Processing::model()->updateByPk($processing->id, array('status' => 10, 'log_compile' => $compile_result, 'result' => 0));
            }
        }

        Processing::model()->updateAll(array('status' => 1, 'log_compile' => NULL, 'tests' => NULL, 'result' => 0), 'status = 2 or status = 3 or status = 4');
		
		gc_collect_cycles();
		return true;
    }

    private function test($processing){
        Processing::model()->updateByPk($processing->id, array('status' => 4));
        $tests = $tests_text = array();
		$result = 0;
        $dir_input = '/var/www/AcmMdC/files/answers/'.$processing->p_id.'/input';
        $dir_output = '/var/www/AcmMdC/files/answers/'.$processing->p_id.'/output';
        $dir_to = '/var/www/AcmMdC/files/testing';

        for ($i=1; $i<=10; $i++){
            $tests[$i] = false;

            copy($dir_input."/".$i.'.txt', $dir_to."/input.txt");
			
			if ($processing->compiler == 'Java'){
				$execite_result = $this->execute_shell(
					"cd ".$dir_to."; ulimit -v ".(1024*$processing->limit_memory*100)."; time sudo time -v -o './log_time.txt' timeout ".($processing->limit_time*10)." java Main;",
					$dir_to
				);
			}else{
				$execite_result = $this->execute_shell(
					"cd ".$dir_to."; ulimit -v ".(1024*$processing->limit_memory)."; time sudo time -v -o './log_time.txt' timeout ".$processing->limit_time." ./1;",
					$dir_to
				);
			}

            if ($execite_result[0] == 'time'){
                $tests_text[$i] = array('time', $execite_result[1]);
            }elseif($execite_result[0] == 'memory'){
				$tests_text[$i] = array('memory', $execite_result[1]);
            }elseif(!file_exists($dir_to.'/output.txt')){
                $tests_text[$i] = array('outputDoesntExist', $execite_result[1]);
            }else{
                $file1 = file($dir_to.'/output.txt');
                $hendle = opendir($dir_output.'/'.$i);
                while ($file = readdir($hendle)) {
                    if (($file!=".") && ($file!="..") && !$tests[$i]) {
                        $file2 = file($dir_output.'/'.$i.'/'.$file);
                        $diff = array_diff($file1, $file2);
                        if(empty($diff)){
                            $tests[$i] = true;
							$result += 10;
                            $tests_text[$i] = array('ok', $execite_result[1]);
                        }
                    }
                }

				if (!$tests[$i]){
                    $tests_text[$i] = array('wrongAnswer', $execite_result[1]);
                }
            }

            @unlink($dir_to."/input.txt");
            @unlink($dir_to."/output.txt");
            @unlink($dir_to."/log_time.txt");
        }
        Processing::model()->updateByPk($processing->id, array('status' => 5, 'tests' => json_encode($tests_text), 'result' => $result));
		$this->clearDir();
    }

    private function execute_shell($cmd, $adr_to){
        ## Connect
        $connection = ssh2_connect('localhost', 23);
        ssh2_auth_password($connection, 'root', 'Vlad77915');

        $stream = ssh2_exec($connection, $cmd);

        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($errorStream, true);
        stream_set_blocking($stream, true);

        $output = stream_get_contents($stream);
        $error = stream_get_contents($errorStream);

        preg_match_all('/0m(.*)s/i', $error, $arr);

        fclose($errorStream);
        fclose($stream);

        $log_time = file_get_contents($adr_to.'/log_time.txt');

        if (substr_count($log_time, 'status 124')){
            return array('time', $arr[1][0]);
        }elseif (substr_count($log_time, 'status 137')){
            return array('memory', $arr[1][0]);
        }else{
            return array('ok', $arr[1][0]);
        }
    }
	
	private function fileName($compiler){
		switch($compiler){
			case "FPC":
				return "1.pas";
				break;
			case "GCC":
				return "1.c";
				break;
			case "G++":
				return "1.cpp";
				break;				
			case "Prolog":
				return "1.pl";
				break;	
			case "Java":
				return "Main.java";
				break;		
		}
	}
	
	private function clearDir(){
		if($handle = opendir('/var/www/AcmMdC/files/testing/'))
		{
			while(false !== ($file = readdir($handle)))
					if($file != "." && $file != "..") unlink('/var/www/AcmMdC/files/testing/'.$file);
			closedir($handle);
		}		
	}	
}