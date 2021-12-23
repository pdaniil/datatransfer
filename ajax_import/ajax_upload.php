<?php
    error_reporting(E_ALL & ~E_NOTICE);
    function writeLog($message, $f, $tmp = null)
    {
        ob_start();
        echo "<br>";
        print_r($message);
        echo $tmp;
        $page = ob_get_contents();
        fwrite($f, $page);
        ob_end_clean();
    }

	if (file_exists($_GET["name"])) 
	{

		$name = $_GET["name"];
		//Конфигурация CMS
		require_once($_SERVER["DOCUMENT_ROOT"]."/config.php");
		$DP_Config = new DP_Config;
		//Подключение к БД
		try
		{
			$db_link = new PDO('mysql:host='.$DP_Config->host.';dbname='.$DP_Config->db, $DP_Config->user, $DP_Config->password);
		}
		catch (PDOException $e) 
		{
			$answer = array();
			$answer["status"] = false;
			$answer["message"] = "No DB connect";
			exit( json_encode($answer) );
		}
		$db_link->query("SET NAMES utf8;");

        //Файл лога
        $f = fopen('log.txt','w');
		$xml = simplexml_load_file($name);

        writeLog("Лог процесса:", $f);

        //Работа с файлом config
		if ($xml->db_version == '1.0') //Новая версия платформы, просто идём по файлу и загружаем данные
		{
            writeLog("Версия ИБ:", $f, $xml->db_version);

            writeLog("Начало работы с файлом config.", $f);
			//Начало работы с файлом config_group
            define("_ASTEXE_", "");
			require_once($_SERVER["DOCUMENT_ROOT"]."/cp/content/control/dp_configeditor.php");
			$config_parameters_query = $db_link->prepare("SELECT * FROM `config_items`;");
			$config_parameters_query->execute();
			while( $item = $config_parameters_query->fetch() )
			{

                //writeLog("Обработка параметра ", $f, $xml->config->{$item["name"]});
				//С некоторыми типами параметров необходимо работать особым образом:
				if ($xml->config->{$item["name"]} == '')
				{
					continue;
				}

				if($item["type"]=="password")//Для паролей: если передан пустой - оставляем как есть
				{
					if($xml->config->{$item["name"]} != "") DP_ConfigEditor::setParameter($item["name"], $xml->config->{$item["name"]});
				}
				else if($item["type"]=="checkbox")//Для чекбоксов приводим к булевому типу
				{
					DP_ConfigEditor::setParameter($item["name"], filter_var($xml->config->{$item["name"]}, FILTER_VALIDATE_BOOLEAN));
				}
				else//Для все остальных типов - как есть
				{
					DP_ConfigEditor::setParameter($item["name"], $xml->config->{$item["name"]});
				}
			}

			//Конец работы с файлом config
			writeLog("Конец работы с файлом config.", $f);

		}


        //Начало работы с БД
        $dictionary_folder = $_SERVER["DOCUMENT_ROOT"].'/'.$DP_Config->backend_dir.'/content/shop/data_transfer/database_transfer/dictionary/'.
            $xml->db_version.'.php';

        if (file_exists($dictionary_folder))
        writeLog("Словарь версии ".$xml->db_version." был успешно подключен.", $f);

        require_once($dictionary_folder);


        $base_file_name = $_SERVER["DOCUMENT_ROOT"].'/'.$DP_Config->backend_dir.'/content/shop/data_transfer/database_transfer/import_classes/base.php';
        require_once($base_file_name);
        $db_link->beginTransaction();
        try
        {
            foreach ($xml as $key => $table)
            {

                $file_name = $_SERVER["DOCUMENT_ROOT"].'/'.$DP_Config->backend_dir.'/content/shop/data_transfer/database_transfer/import_classes/'.$key.'.php';
                if (file_exists($file_name))
                {
                    require_once($file_name);
                    $obj = new $key($table, $dictionary[$key], $f);
                    $obj->putDataIntoTable();
                    $obj->destroy();
                }
            }
        }
        catch (PDOException $e)
        {
            $db_link->rollBack();
            fwrite($f, "<br>".$e->getMessage());
            $awnser =
                [
                    "status" => 501,
                    "response" => $e->getMessage()
                ];
            exit(json_encode($awnser, true));
        }
        catch (Exception $e)
        {
            $db_link->rollBack();
            fwrite($f, "<br>".$e->getMessage());
            $awnser =
                [
                    "status" => 501,
                    "response" => $e->getMessage()
                ];
            exit(json_encode($awnser, true));

        }
        $db_link->commit();
        fwrite($f, "<br>Успешно");


		unlink($name);

        $awnser = [
            "status" => 200,
            "response" => 'success'
        ];
        exit(json_encode($awnser, true));
	} 
	else 
	{
        $awnser =
            [
                "status" => 501,
                "response" => "Не удалось открыть файл"
            ];
        exit(json_encode($awnser, true));
	}
?>