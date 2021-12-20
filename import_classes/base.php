<?php

	class base
	{
		public $data;
		public $dictionary;
		public $log_file;
        public $table_name;
		function __construct( $arr = null, $rules = null, $log_file)
		{
			$this->data = $arr;
			$this->dictionary = $rules;
			$this->log_file = $log_file;
		}
		
		function destroy()
		{
			$this->data = null;
			$this->dictionary = null;
		}

        function sendLog($message)
        {
            $page = "<br>".$message."<br>";
            fwrite ($this->log_file, $page);
        }

		function putDataIntoTable()
		{
			global $db_link;
			global $DP_Config;
			//Если есть скрипт SQL на TRUNCATE, то вначале выполняем его
			if (!is_null($this->SQL_truncate))
			{
				$res = $db_link->prepare($this->SQL_truncate)->execute();
			
				if (!$res)
                {
                    throw new PDOException("Ошибка очистки таблицы ".$this->table_name.".");
                }
                else
                {
                    $this->sendLog("Очистка объекта ".$this->table_name." завершена.");
                }
			}
			
			//Далее идёт скрипт на INSERT, принцип тот же
			if (!is_null($this->SQL_insert))
			{
				$query = $db_link->prepare($this->SQL_insert);	

				foreach($this->data as $value)
				{
					$params = [];
					foreach ($this->dictionary["params"] as $key => $param)
					{
                        if (!is_array($value->{$param}))
                        {
                            $params[] = $value->{$param};
                        }
                        else
                        {
                            //записываем подтаблицу
                            $file_name = $key."_".$param.".php";
                            require_once($file_name);

                            $office_id = $value->id;

                            $item = $value->{$param};
                            foreach ()
                            $subiten = $item['subparams'][$key]
                                $value->{$param}['params'][] = $subiten;

                            $obj = new $key( $value, $value->{$param}, $this->log_file);
                            $obj->putDataIntoTable();
                            $obj->destroy();

                        }

					}
					$res = $query->execute( $params );
					
					if (!$res)
                    {
                        throw new PDOException("Ошибка вставки данных в таблицу ".$this->table_name.".");
                    }

				}
                $this->sendLog("Вставка данных в объект ".$this->table_name." завершена.");
			}
			
			//Далее идёт скрипт на UPDATE
			if (!is_null($this->SQL_update))
			{
				$query = $db_link->prepare($this->SQL_update);
			
				foreach($this->data as $value)
				{
					$params = [];
					foreach ($this->dictionary["params"] as $param)
					{
						$params[] = $value->{$param};
					}
					
					$res = $query->execute( $params );
					
					if (!$res)
                    {
                        throw new PDOException("Ошибка обновления таблицы ".$this->table_name.".");
                    }
				}
                $this->sendLog("Обновление данных объекта ".$this->table_name." завершено.");
			}
			
		}
		
	}

?>