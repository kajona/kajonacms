<?php
/********************************************************************************************************
*	(c) by MulchProductions, www.mulchprod.de															*
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	copy.php																							*
* 	Kopiert die Module in ein System / aus einem System in die Module									*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$													*
********************************************************************************************************/


//Steuerblock
$obj_copy = new class_copy();
echo "<pre>";

if(isset($_GET["aktion"]))
{
	if($_GET["aktion"] == "copy_out")
		$obj_copy->aktion_copy_out();
	elseif ($_GET["aktion"] == "copy_out_downwards")
	    $obj_copy->aktion_copy_out_downwards();
	elseif ($_GET["aktion"] == "copy_out_upwards_svn")
	    $obj_copy->aktion_copy_out_upwards_svn();
	elseif ($_GET["aktion"] == "copy_in")
		$obj_copy->aktion_copy_in();
	elseif ($_GET["aktion"] == "copy_in_downwards")
		$obj_copy->aktion_copy_in_downwards();
	elseif ($_GET["aktion"] == "copy_in_upwards_svn")
		$obj_copy->aktion_copy_in_upwards_svn();

}
else
{
      echo "<h1>This script is deprecated. Use copy2project.php instead!</h1>";
    echo "<h2>Classic: upwards (Zend, ...)</h2>";
	echo "<a href=\"copy.php?aktion=copy_out\">Copy-Out upwards (destination folder: ../)</a>\n\n";
	echo "<a href=\"copy.php?aktion=copy_in\">Copy-In (from upwards(../))</a>\n\n";
	echo "<h2>New: downwards (Eclipse, ...)</h2>";
	echo "<a href=\"copy.php?aktion=copy_out_downwards\">Copy-Out downwards (destination folder: ./kajona/)</a>\n\n";
	echo "<a href=\"copy.php?aktion=copy_in_downwards\">Copy-In (from downwards (./kajona/))</a>\n\n";
	echo "<h2>For SVN Projects: upwards (Eclipse, ...)</h2>";
	echo "<a href=\"copy.php?aktion=copy_out_upwards_svn\">Copy-Out upwards (destination folder: ../trunk/)</a>\n\n";
	echo "<a href=\"copy.php?aktion=copy_in_upwards_svn\">Copy-In (from upwards (../trunk/))</a>\n\n";
}

echo "</pre>";


//Handle-Klasse

class class_copy
{
	private $str_pfad;
	private $str_system_pfad;
	private $str_log;

	public function  __construct()
	{
		//Globale Variablen setzten
		$this->str_pfad = dirname($_SERVER['SCRIPT_FILENAME']);
		$this->str_system_pfad = substr($this->str_pfad, 0, strrpos($this->str_pfad, "/"));
		$this->str_log = "";
	}



	public function aktion_copy_in()
	{
		//Log-Datei auslesen
		$str_temp = trim(file_get_contents($this->str_pfad."/copy.log"));
		//Dateien extrahieren
		$array_dateien = explode("<newfile>", $str_temp);
		$inI = 0;
		//Diese zurueckkopieren
		foreach($array_dateien as $array_datei)
		{
			$array_eine_datei = explode("<to>", $array_datei);
			if($array_eine_datei[0] != "" && basename($array_eine_datei[0]) != "config.php" && basename($array_eine_datei[0]) != "systemlog.log" && basename($array_eine_datei[0]) != "dblog.log" && basename($array_eine_datei[0]) != ".htaccess")
			{
				copy(trim($array_eine_datei[1]), trim($array_eine_datei[0]));
				echo $inI++ . " ". trim($array_eine_datei[1])." --> ".trim($array_eine_datei[0])."\n";
			}
		}
	}

	public function aktion_copy_in_downwards()
	{
		//Log-Datei auslesen
		$str_temp = trim(file_get_contents($this->str_pfad."/copy_down.log"));
		//Dateien extrahieren
		$array_dateien = explode("<newfile>", $str_temp);
		$inI = 0;
		//Diese zurueckkopieren
		foreach($array_dateien as $array_datei)
		{
			$array_eine_datei = explode("<to>", $array_datei);
			if($array_eine_datei[0] != "" && basename($array_eine_datei[0]) != "config.php" && basename($array_eine_datei[0]) != "systemlog.log" && basename($array_eine_datei[0]) != "dblog.log" && basename($array_eine_datei[0]) != ".htaccess")
			{
				copy(trim($array_eine_datei[1]), trim($array_eine_datei[0]));
				echo $inI++ . " ". trim($array_eine_datei[1])." --> ".trim($array_eine_datei[0])."\n";
			}
		}
	}

	public function aktion_copy_in_upwards_svn()
	{
		//Log-Datei auslesen
		$str_temp = trim(file_get_contents(str_replace("/_module", "", $this->str_pfad)."/log/copy_up_svn.log"));
		//Dateien extrahieren
		$array_dateien = explode("<newfile>", $str_temp);
		$inI = 0;
		//Diese zurueckkopieren
		foreach($array_dateien as $array_datei)
		{
			$array_eine_datei = explode("<to>", $array_datei);
			if($array_eine_datei[0] != "" && basename($array_eine_datei[0]) != "config.php" && basename($array_eine_datei[0]) != "systemlog.log" && basename($array_eine_datei[0]) != "dblog.log" && basename($array_eine_datei[0]) != ".htaccess")
			{
				copy(trim($array_eine_datei[1]), trim($array_eine_datei[0]));
				echo $inI++ . " ". trim($array_eine_datei[1])." --> ".trim($array_eine_datei[0])."\n";
			}
		}
	}
	


	public function aktion_copy_out()
	{
		$array_files = $this->get_gesamte_liste("");
		if(!isset($_POST["submit"])) {
            //var_dump($array_files["ordner"]);die();
            echo "<form method=\"POST\" target=\"\">\n";
            foreach ($array_files["ordner"] as $str_ordner)
                if($str_ordner != "kajona")
                    echo "<input type=\"checkbox\" name=\"module[".$str_ordner."]\" value=\"".$str_ordner."\" id=\"".$str_ordner."\" checked=\"checked\" /><label for=\"".$str_ordner."\">".$str_ordner."</label>\n";

            echo "<input type=\"submit\" name=\"submit\" value=\"Copy out\" />\n";
            echo "</form>\n";
		}
        else {
    		//gewünscht module abfragen
    		foreach($array_files["ordner"] as $str_modulordner) {
    			if($str_modulordner != "modul_vorlage" && $str_modulordner != "kajona" && in_array($str_modulordner, $_POST["module"])) {
    				echo "<b>".$str_modulordner."</b>\n";
    				$this->copy_out("/".$str_modulordner, $str_modulordner, 1);
    			}
    		}

    		file_put_contents($this->str_pfad."/copy.log", $this->str_log);
    		$this->updateConfig($this->str_system_pfad."/system/config/config.php");
        }
	}

	public function aktion_copy_out_downwards()
	{
		$array_files = $this->get_gesamte_liste("");
		if(!isset($_POST["submit"])) {
            //var_dump($array_files["ordner"]);die();
            echo "<form method=\"POST\" target=\"\">\n";
            foreach ($array_files["ordner"] as $str_ordner)
                if($str_ordner != "kajona")
                    echo "<input type=\"checkbox\" name=\"module[".$str_ordner."]\" value=\"".$str_ordner."\" id=\"".$str_ordner."\" checked=\"checked\" /><label for=\"".$str_ordner."\">".$str_ordner."</label>\n";

            echo "<input type=\"submit\" name=\"submit\" value=\"Copy out\" />\n";
            echo "</form>\n";
		}
        else {
    		//gewünscht module abfragen
    		foreach($array_files["ordner"] as $str_modulordner) {
    			if($str_modulordner != "modul_vorlage" && $str_modulordner != "kajona" && in_array($str_modulordner, $_POST["module"])) {
        				echo "<b>".$str_modulordner."</b>\n";
    				$this->copy_out_down("/".$str_modulordner, $str_modulordner, 1);
    			}
    		}

    		//ggfs den root-folder anlegen
    		if(!is_dir($this->str_pfad."/kajona"))
    		    mkdir($this->str_pfad."/kajona");

    		chmod($this->str_pfad."/kajona", 0777);

    		file_put_contents($this->str_pfad."/copy_down.log", $this->str_log);
    		$this->updateConfig($this->str_pfad."/kajona/system/config/config.php");
        }
	}

	public function aktion_copy_out_upwards_svn()
	{
		$array_files = $this->get_gesamte_liste("");
		if(!isset($_POST["submit"])) {

            echo "<form method=\"POST\" target=\"\">\n";
            foreach ($array_files["ordner"] as $str_ordner)
                if($str_ordner != "kajona")
                    echo "<input type=\"checkbox\" name=\"module[".$str_ordner."]\" value=\"".$str_ordner."\" id=\"".$str_ordner."\" checked=\"checked\" /><label for=\"".$str_ordner."\">".$str_ordner."</label>\n";

            echo "<input type=\"submit\" name=\"submit\" value=\"Copy out\" />\n";
            echo "</form>\n";
		}
        else {
        	
        	// SVN Struktur Ordner anlegen
        	$str_project_path = str_replace("_module", "", $this->str_pfad);
        	
        	
	        if(!is_dir($str_project_path."branches/"))
				{
					mkdir($str_project_path."branches/");
					chmod($str_project_path."branches/", 0777);
				}
        	
	        if(!is_dir($str_project_path."tags/"))
				{
					mkdir($str_project_path."tags/");
					chmod($str_project_path."tags/", 0777);
				}
        	
	        if(!is_dir($str_project_path."log/"))
				{
					mkdir($str_project_path."log/");
					chmod($str_project_path."log/", 0777);
				}
        	
    		//copy selected modules
    		foreach($array_files["ordner"] as $str_modulordner) {
    			if($str_modulordner != "modul_vorlage" && $str_modulordner != "kajona" && in_array($str_modulordner, $_POST["module"])) {
    				echo "<b>".$str_modulordner."</b>\n";
    				
    				$this->copy_out_up("/".$str_modulordner, $str_modulordner, 1);
    			}
    		}
    		
    		file_put_contents(str_replace("/_module", "", $this->str_pfad)."/log/copy_up_svn.log", $this->str_log);
        }
        
	}	
	
	/**
	 * Kopiert die Dateien aus den Modulen in ein System
	 *
	 */
	public function copy_out($str_ordner, $str_modul, $int_ebene)
	{
		//alle Dateien und Ordner eine Ebene nach oben kopieren
		for($int_i = 0; $int_i <= $int_ebene; $int_i++)
			echo"  ";
		echo substr($str_ordner, strpos($str_ordner, $str_modul)+strlen($str_modul))."\n";
		$array_files_modul = $this->get_gesamte_liste($str_ordner);
		foreach($array_files_modul["dateien"] as $array_modul_datei)
		{
			for($int_i = 0; $int_i <= $int_ebene; $int_i++)
				echo"  ";
			//Alte datei löschen?
			if(is_file(str_replace("/_module/".$str_modul,"", $array_modul_datei["dateipfad"])))
				unlink(str_replace("/_module/".$str_modul,"", $array_modul_datei["dateipfad"]));
			copy($array_modul_datei["dateipfad"], str_replace("/_module/".$str_modul,"", $array_modul_datei["dateipfad"]));
			//Chmod absetzen
			chmod(str_replace("/_module/".$str_modul,"", $array_modul_datei["dateipfad"]), 0777);
			echo $array_modul_datei["dateipfad"] . " --> ".str_replace("/_module/".$str_modul,"", $array_modul_datei["dateipfad"])."\n";
			$this->str_log .= $array_modul_datei["dateipfad"] . "<to>\n".str_replace("/_module/".$str_modul,"", $array_modul_datei["dateipfad"])."\n<newfile>\n";
		}

		foreach ($array_files_modul["ordner"] as $str_modul_ordner)
		{
			//Den Ordner anlegen
			$str_ordner_2 = $this->str_system_pfad.substr($str_ordner, strpos($str_ordner, $str_modul)+strlen($str_modul))."/".$str_modul_ordner;
			if(!is_dir($str_ordner_2))
			{
				mkdir($str_ordner_2);
				chmod($str_ordner_2, 0777);
			}
			$this->copy_out($str_ordner."/".$str_modul_ordner, $str_modul, $int_ebene++);

		}
	}

	/**
	 * Kopiert die Dateien aus den Modulen in ein System --> in den subfolder kajona
	 *
	 */
	public function copy_out_down($str_ordner, $str_modul, $int_ebene)
	{
		//alle Dateien und Ordner eine Ebene nach unten kopieren
		for($int_i = 0; $int_i <= $int_ebene; $int_i++)
			echo"  ";
		echo substr($str_ordner, strpos($str_ordner, $str_modul)+strlen($str_modul))."\n";
		$array_files_modul = $this->get_gesamte_liste($str_ordner);
		foreach($array_files_modul["dateien"] as $array_modul_datei)
		{
			for($int_i = 0; $int_i <= $int_ebene; $int_i++)
				echo"  ";
			//Alte datei löschen?
			if(is_file(str_replace("/_module/".$str_modul,"/_module/kajona", $array_modul_datei["dateipfad"])))
				unlink(str_replace("/_module/".$str_modul,"/_module/kajona", $array_modul_datei["dateipfad"]));
			copy($array_modul_datei["dateipfad"], str_replace("/_module/".$str_modul, "/_module/kajona", $array_modul_datei["dateipfad"]));
			//Chmod absetzen
			chmod(str_replace("/_module/".$str_modul,"/_module/kajona", $array_modul_datei["dateipfad"]), 0777);
			echo $array_modul_datei["dateipfad"] . " --> ".str_replace("/_module/".$str_modul,"/_module/kajona", $array_modul_datei["dateipfad"])."\n";
			$this->str_log .= $array_modul_datei["dateipfad"] . "<to>\n".str_replace("/_module/".$str_modul,"/_module/kajona", $array_modul_datei["dateipfad"])."\n<newfile>\n";
		}

		foreach ($array_files_modul["ordner"] as $str_modul_ordner)
		{

			//Den Ordner anlegen
			$str_ordner_2 = $this->str_pfad."/kajona".substr($str_ordner, strpos($str_ordner, $str_modul)+strlen($str_modul));
			if(!is_dir($str_ordner_2))
			{
				mkdir($str_ordner_2);
				chmod($str_ordner_2, 0777);
			}
			$str_ordner_2 = $this->str_pfad."/kajona".substr($str_ordner, strpos($str_ordner, $str_modul)+strlen($str_modul))."/".$str_modul_ordner;
			if(!is_dir($str_ordner_2))
			{
				mkdir($str_ordner_2);
				chmod($str_ordner_2, 0777);
			}
			$this->copy_out_down($str_ordner."/".$str_modul_ordner, $str_modul, $int_ebene++);
		}
	}

	/**
	 * Kopiert die Dateien aus den Modulen in ein System --> geschwisterordner trunk
	 *
	 */
	public function copy_out_up($str_ordner, $str_modul, $int_ebene)
	{
		//alle Dateien und Ordner eine Ebene nach oben kopieren
		for($int_i = 0; $int_i <= $int_ebene; $int_i++)
			echo"  ";
		
		$array_files_modul = $this->get_gesamte_liste($str_ordner);
		
		foreach($array_files_modul["dateien"] as $array_modul_datei)
		{
			for($int_i = 0; $int_i <= $int_ebene; $int_i++)
				echo"  ";
			
			$str_file_name = basename($array_modul_datei["dateipfad"]);
			$str_directory_source = dirname($array_modul_datei["dateipfad"])."/";
			$str_directory_dest = str_replace("_module/".$str_modul, "trunk", $str_directory_source);
			
			$str_source = $str_directory_source . $str_file_name;
			$str_dest = $str_directory_dest . $str_file_name;
			
			//Alte datei löschen?
			if(is_file($str_dest))
				unlink($str_dest);
			
			copy($str_source, $str_dest);

			//Chmod absetzen
			chmod($str_dest, 0777);
			echo $str_source . " --> ". $str_dest ."\n";
			$this->str_log .= $str_source . "<to>\n". $str_dest ."\n<newfile>\n";
			
		}

		foreach ($array_files_modul["ordner"] as $str_modul_ordner)
		{
			//Den Ordner anlegen
			$str_ordner_2 = str_replace("/_module", "",$this->str_pfad)."/trunk/".substr($str_ordner, strpos($str_ordner, $str_modul)+strlen($str_modul));

			if(!is_dir($str_ordner_2))
			{
				mkdir($str_ordner_2);
				chmod($str_ordner_2, 0777);
			}
			
			$str_ordner_2 = str_replace("/_module", "",$this->str_pfad)."/trunk".substr($str_ordner, strpos($str_ordner, $str_modul)+strlen($str_modul))."/".$str_modul_ordner;
			if(!is_dir($str_ordner_2))
			{
				mkdir($str_ordner_2);
				chmod($str_ordner_2, 0777);
			}
			$this->copy_out_up($str_ordner."/".$str_modul_ordner, $str_modul, $int_ebene++);
		}
	}




	/**
	* @return array
	* @param string $vereichnis
	* @param array $arr_endung
	* @param array $arr_ausschluss
	* @param bool $bit_ordner
	* @param bool $bit_dateien
	* @desc Gibt eine Liste mit den Details des Ordners und darin liegenden Dateien/Ordner zurueck
	*/
	public function get_gesamte_liste($verzeichnis, $arr_endung = array(), $arr_ausschluss = array(), $arr_ausschluss_ordner = array(".svn", ".", ".."), $bit_ordner = true, $bit_dateien = true)
	{
		$arr_return = array( "dateien_anz"  =>  0,
							 "ordner_anz"	=>	0,
							 "dateien"		=>	array(),
							 "ordner"		=>	array()
						    );


		//Als erstes mal checken, ob es das verz. ueberhaupt gibt.
		if(is_dir($this->str_pfad . $verzeichnis))
		{

			//Gut, dann mal einen Handler auf das Verzeichnis erstellen
			if($handler = opendir($this->str_pfad . $verzeichnis))
			{
				while(($eintrag = readdir($handler)) !== false)
				{
					//Datei oder Ordner?

					//Ordner
					if(is_dir($this->str_pfad . $verzeichnis ."/". $eintrag) && $bit_ordner == true)
					{
						//Ist der Ordner ausgeschlossen?
						if(count($arr_ausschluss_ordner) == 0 || !in_array($eintrag, $arr_ausschluss_ordner))
						{
							$arr_return["ordner"][$arr_return["ordner_anz"]] = $eintrag;
							$arr_return["ordner_anz"]++;
						}
					}

					//Datei
					if(is_file($this->str_pfad . $verzeichnis ."/". $eintrag) && $bit_dateien == true)
					{
						$arr_temp = $this->get_datei_details($this->str_pfad.$verzeichnis."/".$eintrag);

						//Ist die Datei ueberhaupt erwuenscht?
						//Ist die Datei ausgeschlossen?
						if(count($arr_ausschluss) == 0 || !in_array($arr_temp["dateityp"], $arr_ausschluss))
						{
							//Dateityp-Begrenzung angegeben?
							if(count($arr_endung) != 0)
							{
								if(in_array($arr_temp["dateityp"], $arr_endung))
								{
									$arr_return["dateien"][$arr_return["dateien_anz"]] = $arr_temp;
									$arr_return["dateien_anz"] ++;
								}
							}
							else
							{
								$arr_return["dateien"][$arr_return["dateien_anz"]] = $arr_temp;
								$arr_return["dateien_anz"]++;
							}
						}
					}
				}
			}
		}

		//Array sortieren
		asort($arr_return["ordner"]);
		asort($arr_return["dateien"]);
		return $arr_return;
	}

	/**
	* @return array
	* @param string $str_datei
	* @desc Liest Detail-Informationen zu einer Datei aus
	*/
	public function get_datei_details($str_datei)
	{
		$arr_return = array();
		if(is_file($str_datei))
		{
			//Dateiname bestimmen
			$int_temp = strrpos($str_datei, "/");
			if($int_temp !== false)
				$arr_return["dateiname"] = substr($str_datei, $int_temp+1);
			else
				$arr_return["dateiname"] = $str_datei;
			//Datei-typ bestimmen
			$int_temp = strrpos($str_datei, ".");
			if($int_temp !== false)
				$arr_return["dateityp"] = substr($str_datei, $int_temp);
			else
				$arr_return["dateityp"] = $str_datei;
			$arr_return["dateityp"] = strtolower($arr_return["dateityp"]);

			//Groe�e bestimmen
			$arr_return["dateigroesse"] = filesize($str_datei);

			//Erstellungsdatum
			$arr_return["dateierstell"] = filemtime($str_datei);

			//aenderungsdatum
			$arr_return["dateiaenderung"] = filectime($str_datei);

			//Letzer Zugriff
			$arr_return["dateizugriff"] = fileatime($str_datei);

			//Pfad
			$arr_return["dateipfad"] = $str_datei;
		}

		return $arr_return;
	}


	public function updateConfig($strFile) {
	    $strContent = file_get_contents($strFile);
	    $strSearch = "/\[\'debuglevel\'\]\s* = 0/";
	    $strReplace = "['debuglevel'] = 1";
	    $strContent = preg_replace($strSearch, $strReplace, $strContent);

	    $strSearch = "/\[\'debuglogging\'\]\s* = 1/";
	    $strReplace = "['debuglogging'] = 2";
	    $strContent = preg_replace($strSearch, $strReplace, $strContent);

	    file_put_contents($strFile, $strContent);
	}
}

?>