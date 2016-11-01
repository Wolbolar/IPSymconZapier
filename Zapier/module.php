<?

	class Zapier extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ConnectParent("{78ECE377-1996-44A1-B4A2-183EF62C3610}", "Zapier Splitter"); //Zapier Splitter
			$this->RegisterPropertyString("zapierwebhook", "");
			$this->RegisterPropertyInteger("selection", 0);
			$this->RegisterPropertyInteger("countsendvars", 0);
			$this->RegisterPropertyInteger("countrequestvars", 0);
			$this->RegisterPropertyBoolean("zapierreturn", false);
			for ($i=1; $i<=15; $i++)
			{
				$this->RegisterPropertyInteger("varvalue".$i, 0);
			}
			for ($i=1; $i<=15; $i++)
			{
				$this->RegisterPropertyBoolean("modulinput".$i, false);
			}
			for ($i=1; $i<=15; $i++)
			{
				$this->RegisterPropertyString("value".$i, "");
			}
			for ($i=1; $i<=15; $i++)
			{
				$this->RegisterPropertyInteger("requestvarvalue".$i, 0);
			}
			for ($i=1; $i<=15; $i++)
			{
				$this->RegisterPropertyBoolean("modulrequest".$i, false);
			}
			
			
		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			//$idstring = $this->RegisterVariableString("ZapierRequest", "Zapier Request", "~String", 2);
			//IPS_SetHidden($idstring, true);
			
			
			
			$iftttass =  Array(
				Array(0, "Trigger Zap",  "Execute", -1)
				);
						
			$this->RegisterProfileIntegerAss("Zapier.ZapTrigger", "Execute", "", "", 0, 0, 0, 0, $iftttass);
			$this->RegisterVariableInteger("ZapierZapTriggerButton", "Zapier Zap Trigger Button", "Zapier.ZapTrigger", 0);
			$this->EnableAction("ZapierZapTriggerButton");
			
			$this->ValidateConfiguration();	
		}
		
		private function ValidateConfiguration()
		{
			$change = false;
			
			$zapierwebhook = $this->ReadPropertyString('zapierwebhook');
			$selection = $this->ReadPropertyInteger("selection");
			$countsendvars = $this->ReadPropertyInteger("countsendvars");
			$countrequestvars = $this->ReadPropertyInteger("countrequestvars");
			
			if ($selection == 1 || $selection == 3) // Senden , Senden / Empfangen
			{
				//Webhook prüfen
				$webhookcheck = false;
				if ($zapierwebhook == "")
					{
						$this->SetStatus(206); //Feld darf nicht leer sein
						//$this->SetStatus(104);
					}
				else
				{
					//Webhook Zapier prüfen
					$webhookcheck = true;
				}
				
				if($countsendvars > 15)
					$countsendvars = 15;
				$varvaluecheck = false;
				$valuecheck = false;
				// Trigger Vars
				for ($i=1; $i<=$countsendvars; $i++)
				{
					${"varvalue".$i} = $this->ReadPropertyInteger('varvalue'.$i);
					${"modulinput".$i} = $this->ReadPropertyBoolean('modulinput'.$i);
					${"value".$i} = $this->ReadPropertyString('value'.$i);
					//Valuecheck
					if(${"modulinput".$i} === false && ${"varvalue".$i} === 0)
					{
						$errorid = 220+$i;
						$this->SetStatus($errorid); //Microsoft Flow Request: select a value or enter value  in module. , errorid 221 - 235
						break;
					}
					else
					{
						$varvaluecheck = true;
					}	
					//check Modul Value
					if (${"modulinput".$i} === true && ${"value".$i} === "")
					{
						$errorid = 240+$i;
						$this->SetStatus($errorid); // Microsoft Flow Request: missing value, enter value in field value, errorid 241 - 255
						break;
					}
					else
					{
						$valuecheck = true;
					}	
				}
				$checkformsend = false;
				if ($webhookcheck === true && $varvaluecheck === true && $valuecheck === true)
				{
					$checkformsend = true;
				}
				elseif ($webhookcheck === true && $countsendvars === 0)
				{
					$this->SetStatus(208); //Mindestens einen Wert auswählen
				}
			}
			
			if ($selection == 2 || $selection == 3) // Empfang , Senden / Empfangen
			{
				if($countrequestvars > 15)
					$countrequestvars = 15;
				$checkformget = false;
				$reqvarvaluecheck = false;
				// Action Vars
				for ($i=1; $i<=$countrequestvars; $i++)
				{
					${"requestvarvalue".$i} = $this->ReadPropertyInteger("requestvarvalue".$i);
					${"modulrequest".$i} = $this->ReadPropertyBoolean("modulrequest".$i);
					$checkformget = false;
					//Valuecheck
					if(${"modulrequest".$i} === false && ${"requestvarvalue".$i} === 0)
					{
						$errorid = 260+$i;
						$this->SetStatus($errorid); //select a value or enter value in module, errorid 261 - 275
						break;
					}
					else
					{
						$checkformget = true;
					}		
				}
			}
			
			if ($selection == 1 && $checkformsend == true) // Senden
			{
				$this->SetStatus(102);
			}
			elseif ($selection == 2 && $checkformget == true) // Empfang
			{
				$this->SetStatus(102);
			}
			elseif ($selection == 3 && $checkformsend == true && $checkformget == true) // Senden / Empfangen
			{
				$this->SetStatus(102);
			}
		}	
		
		protected function SetRequestVariable($key, $value, $type, $i)
		{
			$ident = "ZapierAktionVar".$i;
			$VarID = @$this->GetIDForIdent($ident);	
			if ($VarID === false)
				{
					$VarID = $this->CreateVarbyType($type, $i, $key);
				}
				
			$this->SetVarbyType($type, $VarID, $key, $value);	
		}
		
		protected function CreateVarbyType($type, $i, $key)
		{
			$ident = "ZapierAktionVar".$i;
			if ($type == "string")
				{
					$VarID = $this->RegisterVariableString($ident, $key, "~String", $i);
				}
			elseif ($type == "integer")
				{
					$VarID = $this->RegisterVariableInteger($ident, $key, "", $i);
				}
			elseif ($type == "double") //float
				{
					$VarID = $this->RegisterVariableFloat($ident, $key, "", $i);
				}
			elseif ($type == "boolean")
				{
					$VarID = $this->RegisterVariableBoolean($ident, $key, "~Switch", $i);
				}
			elseif ($type == "NULL")
				{
					$VarID = NULL;
				}
				
				return $VarID;
		}
		
		protected function SetVarbyType($type, $VarID, $key, $value)
		{	
			if ($type == "string")
				{
					SetValueString($VarID, $value);
					IPS_SetInfo ($VarID, $key);
				}
			elseif ($type == "integer")
				{
					SetValueInteger($VarID, $value);
					IPS_SetInfo ($VarID, $key);
				}
			elseif ($type == "double") //float
				{
					SetValueFloat($VarID, $value);
					IPS_SetInfo ($VarID, $key);
				}
			elseif ($type == "boolean")
				{
					SetValueBoolean($VarID, $value);
					IPS_SetInfo ($VarID, $key);
				}
			elseif ($type == "NULL")
				{
					// nichts
				}
				
				return $VarID;
		}
		
		protected function WriteValues($valuesjson)
		{
			$values = json_decode($valuesjson, true);
			$countvalues = count($values);
			$countrequestvars = $this->ReadPropertyInteger('countrequestvars');
			if ( $countvalues == $countrequestvars)
			{
				$i = 1;
				foreach ($values as $key => $value)
					{
						$type = gettype($value);// Typ prüfen
						$requestvarvalue = $this->ReadPropertyInteger('requestvarvalue'.$i);  // Prüfen ob Modulvariable oder Var anlegen
						if (  $requestvarvalue == 0)
							{	
								$this->SetRequestVariable($key, $value, $type, $i);
							}
						else
							{
								$checkvartype = $this->CompareVartype($type, $requestvarvalue);
								if ($checkvartype)
								{
									SetValue($requestvarvalue, $value);
								}
								else
								{
									IPS_LogMessage("Flow:", "Es wurde kein Wert für ".$value." gesetzt, Variablentyp stimmt nicht mit Wert überein.");
								}
							}
						$i = $i+1;
					}
			 }
			else
			{
				echo "Die Anzahl der Variablen stimmt nicht mit der übermittelten Anzahl an Werten überein!";
				IPS_LogMessage("Zapier:", "Es wurden keine Werte gesetzt.");
				IPS_LogMessage("Zapier:", "Die Anzahl der Variablen stimmt nicht mit der übermittelten Anzahl an Werten überein!");
			}
		}
		
		protected function CompareVartype($type, $requestvarvalue)
		{
				$varinfo = (IPS_GetVariable($requestvarvalue));
				$vartype =  $varinfo["VariableType"];
				if ($vartype == 0) //bool
				{
					$ipsvartype = "boolean";
				}
				elseif ($vartype == 1) //integer
				{
					$ipsvartype = "integer";
				}
				elseif ($vartype == 2) //float
				{
					$ipsvartype = "double";
				}
				elseif ($vartype == 3) //string
				{
					$ipsvartype = "string";
				}
				
				if ($type ===  $ipsvartype)
				{
					return true;
				}
				else
				{
					return false;
				}
		}
		
		protected function SetupDataScript()
		{
			//prüfen ob Script existent
			$SkriptID = @$this->GetIDForIdent("ZapierGetData");
				
			if ($SkriptID === false)
				{
					$SkriptID = $this->RegisterScript("ZapierGetData", "Zapier Get Data", $this->CreateDataScript(), 3);
					IPS_SetHidden($SkriptID, true);
					$this->SetZapierDataEvent($SkriptID);
				}
			else
				{
					//echo "Die Skript-ID lautet: ". $SkriptID;
				}	
		}
		
		protected function SetZapierDataEvent(integer $SkriptID)
		{
			//prüfen ob Event existent
			$ParentID = $SkriptID;

			$EreignisID = @($this->GetIDForIdent('EventZapierGetData'));
			if ($EreignisID === false)
				{
					$EreignisID = IPS_CreateEvent (0);
					IPS_SetName($EreignisID, "Event Zapier Get Data");
					IPS_SetIdent ($EreignisID, "EventZapierGetData");
					IPS_SetEventTrigger($EreignisID, 0,  $this->GetIDForIdent('ZapierRequest'));   //bei Variablenaktualisierung
					IPS_SetParent($EreignisID, $ParentID);
					IPS_SetEventActive($EreignisID, true);             //Ereignis aktivieren	
				}
				
			else
				{
				//echo "Die Ereignis-ID lautet: ". $EreignisID;	
				}
		}
		
		protected function CreateDataScript()
		{
			$Script = '<?
 $zapierdatajson = GetValueString('.$this->GetIDForIdent("ZapierRequest").');
 $zapierdata = json_decode($zapierdatajson); // Standard Objekt
 //$zapierdata = json_decode($zapierdatajson, true); // Array
 
 //Standard Objekt oder Array auslesen
 foreach ($zapierdata as $key=>$data)
 {
 	 echo $key." => ".$data."\n";
	 //add command here
 }
 ?>';
			return $Script;
		}
		
		
		protected function ConvertVarString($objid)
		{
			$vartype = IPS_GetVariable($objid)['VariableType'];
			if ($vartype === 0)//Boolean
			{
			$value = GetValueBoolean($objid);// Boolean umwandeln in String
			$value = ($value) ? 'true' : 'false';
			}
			elseif($vartype === 1)//Integer
			{
				$value = strval(GetValueInteger($objid));   // Integer Umwandeln in String
			}
			elseif($vartype === 2)//Float
			{
				$value = strval(GetValueFloat($objid)); //Float umwandeln in String
			}
			elseif($vartype === 3)//String
			{
				$value = GetValue($objid);  //string ok
			}
			return $value;
			
		}
		
		

		public function TriggerZap()
		{
			$zapierwebhook = $this->ReadPropertyString('zapierwebhook');
			$countsendvars = $this->ReadPropertyInteger("countsendvars");
			
			// Trigger Vars
			for ($i=1; $i<=$countsendvars; $i++)
			{
				${"modulinput".$i} = $this->ReadPropertyBoolean('modulinput'.$i);
				if (${"modulinput".$i})
				{
					${"value".$i} = $this->ReadPropertyString('value'.$i);
					${"key".$i} = "value".$i;
				}
				else 
				{
					${"objidvalue".$i} = $this->ReadPropertyInteger('varvalue'.$i);
					${"value".$i} = GetValue(${"objidvalue".$i});
					${"key".$i} = IPS_GetName(${"objidvalue".$i});
					//${"value".$i} = $this->ConvertVarString(${"objidvalue".$i});
				}
			}
			
			$values = array();
			for ($i=1; $i<=$countsendvars; $i++)
			{
				$values["value".$i] = ${"value".$i};
			}
			$values_string = json_encode($values);
			$zapierreturn = $this->SendZapTrigger($zapierwebhook, $values_string);
			return $zapierreturn;
		}


		public function SendZapTrigger(string $zapierwebhook, string $values)
		{
			
			$values = json_decode($values, true);
			$payload = array("zapierwebhook" => $zapierwebhook, "values" => $values);
						
			//an Splitter schicken
			$result = $this->SendDataToParent(json_encode(Array("DataID" => "{1AF4FDAA-056F-4985-AE25-B4A2C742771F}", "Buffer" => $payload))); //Zapier Interface GUI
			return $result;
		}
		
		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			$objectid = $data->Buffer->objectid;
			$values = $data->Buffer->values;
			$valuesjson = json_encode($values);
			if (($this->InstanceID) == $objectid)
			{
				//Parse and write values to our variables
				$this->WriteValues($valuesjson);
				//SetValue($this->GetIDForIdent("ZapierRequest"), $valuesjson);
			}	
		}
		
		public function RequestAction($Ident, $Value)
		{
			switch($Ident) {
				case "ZapierZapTriggerButton":
					SetValue($this->GetIDForIdent("ZapierZapTriggerButton"), $Value);
					$zapierreturn = $this->TriggerZap();
					$zapierreturnvis = $this->ReadPropertyBoolean('zapierreturn');
					if ($zapierreturnvis === true)
					{
						$InstanzenListe = IPS_GetInstanceListByModuleID("{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}");
						foreach ($InstanzenListe as $InstanzID)
						{
							WFC_SendNotification($InstanzID, 'Zapier', $zapierreturn, 'Execute', 4);
						}		
					}
					
					break;	
				default:
					throw new Exception("Invalid ident");
			}
		}
		
		protected function GetUsernamePassword()
		{
			$objid = $this->GetIOObjectID();
			$username = IPS_GetProperty($objid, "username");
			$password = IPS_GetProperty($objid, "password");
			$webhooksettings = array ("username" => $username, "password" => $password);
			return $webhooksettings;		
		}


		protected function GetIOObjectID()
		{
			$InstanzenListe = IPS_GetInstanceListByModuleID("{C60B0A79-8929-47AE-A37E-6A655FC8D56C}");
			foreach ($InstanzenListe as $InstanzID)
				{
					return $InstanzID;
				}
		}
		
		//Configuration Form
		public function GetConfigurationForm()
		{
			$selection = $this->ReadPropertyInteger("selection");
			$countsendvars = $this->ReadPropertyInteger("countsendvars");
			$countrequestvars = $this->ReadPropertyInteger("countrequestvars");
			$formhead = $this->FormHead();
			$formstatus = $this->FormStatus();
			$formsend = $this->FormSend($countsendvars);
			$formget = $this->FormGet($countrequestvars);
			/*
			if ($selection == 2)
			{
				$formget = substr($this->FormGet($countrequestvars), 0, -1); // letztes Komma entfernen
			}
			else
			{
				$formget = $this->FormGet($countrequestvars);
			}
			*/
			
			
			$formreturn = '{ "type": "Label", "label": "Return Message from Zapier" },
				{
                    "name": "zapierreturn",
                    "type": "CheckBox",
                    "caption": "Zapier Return"
                },';
			$formelementsend = '{ "type": "Label", "label": "__________________________________________________________________________________________________" }';	
			if($selection == 0)// keine Auswahl
			{
				return	'{ '.$formhead.'],'.$formstatus.' }';
			}
			
			elseif ($selection == 1) // Senden 
			{
				$formactions = $this->FormActions(1, $countrequestvars);
				return	'{ '.$formhead.','.$formsend.$formreturn.$formelementsend.'],'.$formactions.','.$formstatus.' }';
			}
			
			elseif ($selection == 2) // Empfangen 
			{
				$formactions = $this->FormActions(2, $countrequestvars);
				return	'{ '.$formhead.','.$formget.$formelementsend.'],'.$formactions.','.$formstatus.' }';
			}
			
			elseif ($selection == 3) // Senden / Empfangen
			{
				$formactions = $this->FormActions(3, $countrequestvars);
				return	'{ '.$formhead.','.$formsend.$formget.$formreturn.$formelementsend.'],'.$formactions.','.$formstatus.' }';
			}
		
		}
		
		protected function FormSend($countsendvars)
		{
			$form = '{ "type": "Label", "label": "ZAPIER TRIGGER____________________________________________________________________________________________" },
			{ "type": "Label", "label": "Zapier webhook, type catch" },
		{ "name": "zapierwebhook", "type": "ValidationTextBox", "caption": "Zapier webhook URL" },
		{ "type": "Label", "label": "number of variables for trigger Zapier (max 15)" },
		{ "type": "NumberSpinner", "name": "countsendvars", "caption": "number of variables" },'
		.$this->FormSendVars($countsendvars);
			return $form;
		}
		
		protected function FormSendVars($countsendvars)
		{
			if ($countsendvars > 0)
			{
				if($countsendvars > 15)
				$countsendvars = 15;
				$form = '{ "type": "Label", "label": "variables with values for Zapier" },';
				for ($i=1; $i<=$countsendvars; $i++)
				{
					$form .= '{ "type": "SelectVariable", "name": "varvalue'.$i.'", "caption": "value '.$i.'" },';
				}
				$form .= '{ "type": "Label", "label": "alternative leave variable empty und click check mark" },';
				for ($i=1; $i<=$countsendvars; $i++)
				{
					$form .= '{
						"name": "modulinput'.$i.'",
						"type": "CheckBox",
						"caption": "use modul value '.$i.'"
					},	
			{ "name": "value'.$i.'", "type": "ValidationTextBox", "caption": "value '.$i.'" },';
				}
			}
			else
			{
				$form = "";
			}
			
			return $form;
		}
		
		protected function FormGet($countrequestvars)
		{			 
			$form = '{ "type": "Label", "label": "ZAPIER ACTION_____________________________________________________________________________________________" },
			{ "type": "Label", "label": "variables with values for Zapier" },
			{ "type": "Label", "label": "number of variables for action from Zapier (max 15)" },
			{ "type": "NumberSpinner", "name": "countrequestvars", "caption": "number of variables" },'
			.$this->FormGetVars($countrequestvars);
			return $form;
		}
		
		protected function FormGetVars($countrequestvars)
		{
			if ($countrequestvars > 0)
			{
				if($countrequestvars > 15)
				$countrequestvars = 15;
				$form = '';
				for ($i=1; $i<=$countrequestvars; $i++)
				{
					$form .= '{ "type": "SelectVariable", "name": "requestvarvalue'.$i.'", "caption": "value '.$i.'" },';
				}
				$form .= '{ "type": "Label", "label": "alternative leave variable empty und click check mark for creating a new variable" },';
				for ($i=1; $i<=$countrequestvars; $i++)
				{
					$form .= '{
						"name": "modulrequest'.$i.'",
						"type": "CheckBox",
						"caption": "module create variable for value '.$i.'"
					},';
				}
			}
			else
			{
				$form = "";
			}
			return $form;
		}
		
		protected function FormHead()
		{
			$form = '"elements":
	[
		{ "type": "Label", "label": "Connection from IP-Symcon to Zapier" },
		{ "type": "Label", "label": "https://zapier.com" },
		{ "type": "Label", "label": "communication type with Zapier: send, receive, send/receive" },
		{ "type": "Select", "name": "selection", "caption": "communication",
    "options": [
        { "label": "Please select", "value": 0 },
        { "label": "Send", "value": 1 },
        { "label": "Receive", "value": 2 },
        { "label": "Send/Receive", "value": 3 }
    ]
}';
			// End ]
			return $form;
		}
		
		protected function FormActions($type, $countrequestvars)
		{
			if ($type == 1) // Senden
			{
				$form = '"actions": [{ "type": "Label", "label": "configuration Zapier:" },
				{ "type": "Label", "label": "trigger configuration Zapier:" },
				{ "type": "Label", "label": " - Catch Hook" },
				{ "type": "Label", "label": " - View Hook, copy to clipboard and paste in the module field Zapier webhook" },
				{ "type": "Label", "label": " - set module values with Übernehmen" },
				{ "type": "Label", "label": " - Edit Options, IPS4" },
				{ "type": "Label", "label": " - Continue" },
				{ "type": "Label", "label": " - Trigger Zap with module" },
				{ "type": "Label", "label": " - trigger should registered by Zapier, save trigger continue with action of your choice" },
				{ "type": "Label", "label": "______________________________________________________________________________________________________" },				
				{ "type": "Label", "label": "Trigger Zapier Zap" },
				{ "type": "Button", "label": "Trigger Zap", "onClick": "Zapier_TriggerZap($id);" } ]';
				return  $form;
			}
			elseif ($type == 2) // Empfangen
			{
				$form = '"actions": [ { "type": "Label", "label": "action configuration Zapier:" },
				{ "type": "Label", "label": " - Type POST" },
				{ "type": "Label", "label": " - Edit Template:" },
				{ "type": "Label", "label": "   - URL: '.$this->GetIPSConnect().'/hook/Zapier" },
				{ "type": "Label", "label": "   - Payload Type: form" },
				{ "type": "Label", "label": "     username is set, for individual username set username in Zapier IO" },
				{ "type": "Label", "label": "     password is set, for individual password set password in Zapier IO" },
				{ "type": "Label", "label": "   - Data:" },
				{ "type": "Label", "label": "     zapierusername     '.$this->ZapierConfigAuthUser().'" },
				{ "type": "Label", "label": "     zapierpassword     '.$this->ZapierConfigAuthPassword().'" },
				{ "type": "Label", "label": "     objectid                '.$this->InstanceID.'" },
				{ "type": "Label", "label": "     example values begin and end with curly brackets" },
				{ "type": "Label", "label": "     values              {\"keyvalue1\":\"value1string\",\"keyvalue2\":value2float,\"keyvalue3\":value3int,\"keyvalue4\":value4bool}"},
				{ "type": "Label", "label": "     put keys always inside \"\", string value inside \"\", boolean, integer and float values without \"\"" },
				{ "type": "Label", "label": "     Wrap Request In Array no" },
				{ "type": "Label", "label": "     Unflatten          yes" },
				{ "type": "Label", "label": "   - Continue" } ]';
				return  $form;
			}
			// '.$this->ZapierWebhookConfigRequest($countrequestvars).'
			elseif ($type == 3) // Senden / Empfangen
			{
				$form = '"actions": [ { "type": "Label", "label": "configuration Zapier:" },
				{ "type": "Label", "label": "trigger configuration Zapier:" },
				{ "type": "Label", "label": " - Catch Hook" },
				{ "type": "Label", "label": " - View Hook, copy to clipboard and paste in the module field Zapier webhook" },
				{ "type": "Label", "label": " - set module values with Übernehmen" },
				{ "type": "Label", "label": " - Edit Options, IPS4" },
				{ "type": "Label", "label": " - Continue" },
				{ "type": "Label", "label": " - Trigger Zapier Zap" },
				{ "type": "Label", "label": " - trigger should registered by Zapier, save trigger continue with action of your choice" },
				{ "type": "Label", "label": "______________________________________________________________________________________________________" },
				{ "type": "Label", "label": "action configuration Zapier:" },
				{ "type": "Label", "label": " - Type POST" },
				{ "type": "Label", "label": " - Edit Template:" },
				{ "type": "Label", "label": "   - URL: '.$this->GetIPSConnect().'/hook/Zapier" },
				{ "type": "Label", "label": "   - Payload Type: form" },
				{ "type": "Label", "label": "     username is set, for individual username set username in Zapier IO" },
				{ "type": "Label", "label": "     password is set, for individual password set password in Zapier IO" },
				{ "type": "Label", "label": "   - Data:" },
				{ "type": "Label", "label": "     zapierusername     '.$this->ZapierConfigAuthUser().'" },
				{ "type": "Label", "label": "     zapierpassword     '.$this->ZapierConfigAuthPassword().'" },
				{ "type": "Label", "label": "     objectid                '.$this->InstanceID.'" },
				{ "type": "Label", "label": "     example values begin and end with curly brackets" },
				{ "type": "Label", "label": "     values              {\"keyvalue1\":\"value1string\",\"keyvalue2\":value2float,\"keyvalue3\":value3int,\"keyvalue4\":value4bool}"},
				{ "type": "Label", "label": "     put keys always inside \"\", string value inside \"\", boolean, integer and float values without \"\"" },
				{ "type": "Label", "label": "     Wrap Request In Array no" },
				{ "type": "Label", "label": "     Unflatten          yes" },
				{ "type": "Label", "label": "   - Continue" },
				{ "type": "Label", "label": "______________________________________________________________________________________________________" },
				{ "type": "Label", "label": "Trigger Zapier Zap" },
				{ "type": "Button", "label": "Trigger Zap", "onClick": "Zapier_TriggerZap($id);" } ]';
				return  $form;
			}
		}
		
		
		protected function ZapierWebhookConfigRequest($countrequestvars)
		{
			if ($countrequestvars == 0)
			{
				$form =  '{ "type": "Label", "label": "         values  please select at least one value" }';
			}
			else
			{	
				//{"actions":[ {"type":"Label","label":"values     {\"value1\":\"value2\",\"value3\":\"value4\"}"} ]}
				$form =  '{ "type": "Label", "label": "         values              {';
				for ($i=1; $i<=4; $i++)
				{
					$form .= "\\\"keyvalue".$i."\\\":\\\"value".$i."\\\",";
				}
				$form = substr($form, 0, -1);
				$form .= ' }"},';
			}
			return $form;
		}
		
		protected function ZapierConfigAuthUser()
		{
			$webhooksettings =	$this->GetUsernamePassword();
			$username = $webhooksettings["username"];
			$password = $webhooksettings["password"];
			return $username;
		}
		
		protected function ZapierConfigAuthPassword()
		{
			$webhooksettings =	$this->GetUsernamePassword();
			$password = $webhooksettings["password"];
			return $password;
		}
		
	protected function FormStatus()
		{
			$form = '"status":
            [
                {
                    "code": 101,
                    "icon": "inactive",
                    "caption": "Creating instance."
                },
				{
                    "code": 102,
                    "icon": "active",
                    "caption": "Zapier created."
                },
				'.$this->FormStatusErrorSelectorEnterHTTP().'
                {
                    "code": 104,
                    "icon": "inactive",
                    "caption": "interface closed."
                },
				{
                    "code": 201,
                    "icon": "inactive",
                    "caption": "select number of values in module."
                },
				'.$this->FormStatusErrorSelectorEnter().'
				{
                    "code": 206,
                    "icon": "error",
                    "caption": "Zapier Webhook URL field must not be empty."
                },
				'.$this->FormStatusErrorMissingValueinField().'
				{
                    "code": 207,
                    "icon": "error",
                    "caption": "Zapier URL not valid."
                },
				{
                    "code": 208,
                    "icon": "error",
                    "caption": "Select min one Value."
                }
			
            ]';
			return $form;
		}

		protected function FormStatusErrorSelectorEnter() // errorid 221 - 235
		{
			$form = "";
			for ($i=1; $i<=15; $i++)
			{
				$errorid = 220+$i;
				$form .= '{
                    "code": '.$errorid.',
                    "icon": "error",
                    "caption": "Zapier Trigger: select a value '.$i.' or enter value '.$i.' in module."
                },'; 
			}
			return $form;
		}
		
		protected function FormStatusErrorMissingValueinField() // errorid 241 - 255
		{
			$form = "";
			for ($i=1; $i<=15; $i++)
			{
				$errorid = 240+$i;
				$form .= '{
                    "code": '.$errorid.',
                    "icon": "error",
                    "caption": "Zapier Trigger: missing value, enter value in field value '.$i.'"
                },'; 
			}
			return $form;
		}
		
		protected function FormStatusErrorSelectorEnterHTTP() // errorid 261 - 275
		{
			$form = "";
			for ($i=1; $i<=15; $i++)
			{
				$errorid = 260+$i;
				$form .= '{
                    "code": '.$errorid.',
                    "icon": "error",
                    "caption": "Zapier Action: select a value '.$i.' or enter value '.$i.' in module."
                },'; 
			}
			return $form;
		}	
	
		// IP-Symcon Connect auslesen
		protected function GetIPSConnect()
		{
			$InstanzenListe = IPS_GetInstanceListByModuleID("{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}");
			foreach ($InstanzenListe as $InstanzID) {
				$ConnectControl = $InstanzID;
			} 
			$connectinfo = CC_GetUrl($ConnectControl);
			if ($connectinfo == false || $connectinfo == "")
				$connectinfo = 'https://<IP-Symcon Connect>.ipmagic.de';
			return $connectinfo;
		}
		
		//Profile
		protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
		{
			
			if(!IPS_VariableProfileExists($Name)) {
				IPS_CreateVariableProfile($Name, 1);
			} else {
				$profile = IPS_GetVariableProfile($Name);
				if($profile['ProfileType'] != 1)
				throw new Exception("Variable profile type does not match for profile ".$Name);
			}
			
			IPS_SetVariableProfileIcon($Name, $Icon);
			IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
			IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
			IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
			
		}
		
		protected function RegisterProfileIntegerAss($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Associations)
		{
			if ( sizeof($Associations) === 0 ){
				$MinValue = 0;
				$MaxValue = 0;
			} 
			/*
			else {
				//undefiened offset
				$MinValue = $Associations[0][0];
				$MaxValue = $Associations[sizeof($Associations)-1][0];
			}
			*/
			$this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits);
			
			//boolean IPS_SetVariableProfileAssociation ( string $ProfilName, float $Wert, string $Name, string $Icon, integer $Farbe )
			foreach($Associations as $Association) {
				IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}
			
		}
	
	}

?>
