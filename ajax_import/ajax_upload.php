<?php
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

			//Начало работы с БД
			//Все изменения делаем через транзакции
            writeLog("Начало работы с бд.", $f);
            try
            {
                $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db_link->beginTransaction();

                writeLog("sms_api start", $f);

                $SQL_select = "SELECT `id` FROM `sms_api` WHERE `active` = 1;";
                $query = $db_link->prepare($SQL_select);
                if (!$query->execute())
                {
                    throw new PDOException("Ошибка при чтении таблицы sms_api");
                }
                $result = $query->fetch();

                if (!is_null($result["id"]))
                {
                    $SQL_update = "UPDATE `sms_api` SET `active` = 0 WHERE `id` = ?;";
                    $query = $db_link->prepare($SQL_update);

                    if (!$query->execute( array($result["id"]) ))
                    {
                        throw new PDOException("Ошибка при изменении активного handler.");
                    }

                }

                $SQL_update = "UPDATE `sms_api` SET `parameter_values` = ?, `active` = 1 WHERE `handler` = ?;";

                $query = $db_link->prepare($SQL_update);

                if (!$query->execute( array( $xml-> ) ))

                writeLog("sms_api success end");


                $db_link->commit();
            }
            catch (PDOException $e)
            {
                $db_link->rollBack();
                $awnser =
                [
                    "status" => 501,
                    "response" => $e->getMessage()
                ];
                exit(json_encode($awnser, true));
            }
		}
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