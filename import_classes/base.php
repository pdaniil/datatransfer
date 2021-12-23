<?php
    error_reporting(0);
	class base
    {
        public $data;
        public $dictionary;
        public $log_file;
        public $table_name;
        public $sub_param;
        public $from_parrent;

        function __construct($arr = null, $rules = null, $log_file, $from_parrent = null, $sub_param = null)
        {
            $this->data = $arr;
            $this->dictionary = $rules;
            $this->log_file = $log_file;

            if (!is_null($from_parrent) && !is_null($sub_param)) {
                $this->sub_param = $sub_param;
                $this->from_parrent = true;
            }
        }

        function destroy()
        {
            $this->data = null;
            $this->dictionary = null;
        }

        function sendLog($message, $is_array = null)
        {
            if (is_null($is_array)) {
                $page = "<br>" . $message . "<br>";
                fwrite($this->log_file, $page);
            } else {
                ob_start();
                echo "<pre>";
                print_r($message);
                echo "</pre>";
                $page = ob_get_contents();
                ob_end_clean();
                fwrite($this->log_file, $page);
            }

        }

        function startSubClass($table, $dictionary, $file_name, $class_name, $sub_param)
        {
            global $DP_Config;
            if (file_exists($file_name)) {
                require_once($file_name);
                $obj = new  $class_name ($table->{$key}[0], $dictionary, $this->log_file, 1, $sub_param);
                $obj->putDataIntoTable();
                $obj->destroy();
            }
        }

        function getParams()
        {

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

            $SQL = (!is_null($this->SQL_insert) ? $this->SQL_insert : $this->SQL_update);
            $this->sendLog($SQL);
			//Далее идёт скрипт на INSERT UPDATE, принцип тот же
			if (!is_null($SQL))
			{
				$query = $db_link->prepare($SQL);

				foreach($this->data as $value)
				{
					$params = [];

                    foreach ($this->dictionary["params"] as $key => $param)
                    {
                        if (!is_array($param))
                        {
                            $params[] = $value->{$param};
                        }
                        else
                        {
                            //Если имеем вложенную структуру
                            //value->{$key} - наши данные в виде массива
                            //$param - передаём весь массив из словаря, его структура - "params" - то, что нам нужно будет из таблицы, которую передаём.
                            //"parent_params" - берём на этой стадии, в "потомке" использоваться эти данные не будут
                            //$value->{$parent_param} - данные из родителя (текущей таблицы), которые мы передаём во вложенную таблицу
                            //$from_parent - флаг, который означает, что переходим из родителя, а не напрямую из ajax_upload
                            $sub_param = [];
                            foreach ($param["parent_params"] as $parent_param) {
                                if (isset($value->{$parent_param})) {
                                    $sub_param[$parent_param] = $value->{$parent_param};
                                } else {
                                    $sub_param[$parent_param] = $this->sub_param[$parent_param];
                                }
                            }
                            //Имя вложенного файла будет строиться из текущего table_name + _ + $key
                            $file_name = $_SERVER["DOCUMENT_ROOT"].'/'.$DP_Config->backend_dir.'/content/shop/data_transfer/database_transfer/import_classes/'.$this->table_name."_".$key.'.php';

                            if (file_exists($file_name))
                            {
                                require_once($file_name);
                                $file_name = $this->table_name."_".$key;
                                $obj = new  $file_name ($value->{$key}[0], $param, $this->log_file, 1, $sub_param);
                                $obj->putDataIntoTable();
                                $obj->destroy();
                            }
                        }
                    }

                    //Если мы уже во вложенной структуре, то добавляем к параметрам параметры родителя
                    if (!is_null($this->from_parrent))
                    {
                        if (is_array($this->sub_param))
                        {
                            foreach ($this->sub_param as $sub)
                            {
                                $params[] = $sub[0];
                            }
                        }
                        else
                        {
                            throw new Exception("В качестве параметров родителя ".$this->table_name." был передан не массив.");
                        }
                    }

                    $res = $query->execute( $params );

					if (!$res)
                    {
                        ob_start();
                        echo "<pre>";
                        print_r($query->errorInfo());
                        echo "</pre>";
                        $page = ob_get_contents();
                        ob_end_clean();

                        throw new PDOException("Ошибка вставки данных в таблицу ".$this->table_name.".<br>Текст ошибки: ".$page);
                    }

				}
                $this->sendLog("Вставка данных в объект ".$this->table_name." завершена.");
			}


		}
		
	}

