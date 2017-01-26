<?php

namespace Api\Ares;

class XML 
{
    protected $connection;
    protected $requestUrl;
    protected $ico;
    protected $name;
    protected $xmlData;
    protected $outputData = array();

    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    public function setName($name)
    {
        $name = trim($name);
        if(mb_strlen($name) < 2)
        {
            return false;
        }
        $this->name = $name;
        return true;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setIco($ico)
    {
        $ico = preg_replace('#\s+#', '', $ico);

        // kontrola tvaru ICO
        if (!preg_match('#^\d{8}$#', $ico)) 
        {
            return false;
        }
        $this->ico = $ico;
        
        return true;
    }

    public function getIco()
    {
        return $this->ico;
    }
    
    public function setRequestUrl($requestUrl, $requestData)
    {
        $requestData = urlencode(mb_convert_encoding($requestData, "ISO-8859-2"));
        $this->requestUrl = $requestUrl . $requestData;
        return;
    }
    
    protected function verifyIco()
    {
        $ico = $this->getIco();

        // test kontrolního součtu
        $a = 0;
        for ($i = 0; $i < 7; $i++) 
        {
            $a += $ico[$i] * (8 - $i);
        }

        $a = $a % 11;
        if ($a === 0) 
        {
            $c = 1;
        } 
        elseif ($a === 1) 
        {
            $c = 0;
        } 
        else 
        {
            $c = 11 - $a;
        }

        if((int) $ico[7] === $c)
        {
            return true;
        }
        return false;
    }
    
    protected function loadXMLData()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->requestUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $xmlData = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($httpCode == 200)
        {
            $this->xmlData = $xmlData;
            return true;
        }
        return false;
    }
    
    protected function parseXMLDetail()
    {
        $xml = simplexml_load_string($this->xmlData);
        if($xml === false)
        {
            return false;
        }
        
        $xmlNamespaces = $xml->getDocNamespaces();
        $xmlAresData = $xml->children($xmlNamespaces['are']);
        $element = $xmlAresData->children($xmlNamespaces['D'])->VBAS;
        
        $output = array();
        if(strval($element->ICO) == $this->ico)
        {
            $output['ico'] 	= strval($element->ICO);
            $output['dic'] 	= strval($element->DIC);
            $output['firma']    = strval($element->OF);
            $output['ulice']	= strval($element->AA->NU);
            $output['cp1']      = strval($element->AA->CD);
            $output['cp2']      = strval($element->AA->CO);
            $output['mesto']	= strval($element->AA->N);
            $output['psc']	= strval($element->AA->PSC);
            
            $this->outputData['data'] = $output;
            return true;
        } 
        
        return false;
    }
    
    protected function parseXMLList()
    {
        $xml = simplexml_load_string($this->xmlData);
        if($xml === false)
        {
            return false;
        }
        
        $xmlNamespaces = $xml->getDocNamespaces();
        $xmlAresData = $xml->children($xmlNamespaces['are']);
        
        $output = array();
        foreach($xmlAresData->children($xmlNamespaces['dtt'])->V as $elements)
        {
            foreach($elements->S as $element)
            {
                $partOutput = array();
                $partOutput['ico']      = strval($element->ico);
                $partOutput['dic']      = '';
                if(strval($element->p_dph) != '')
                {
                    $dicElement = explode('=', strval($element->p_dph));
                    $partOutput['dic']  = $dicElement[1];
                }
                $partOutput['firma']    = strval($element->ojm);
                $partOutput['adresa']   = strval($element->jmn);
                $output[] = $partOutput;
            }
        }
        if(count($output) < 1)
        {
            return false;
        }
        $this->outputData['data'] = $output;
        return true;
    }
    
    public function getDataByIco($requestUrl, $requestData)
    {
        $this->outputData['status'] = false;
        if(!$this->setIco($requestData))
        {
            $this->outputData['message'] = 'Nesprávné IČO, nesedí formát.';
            return false;
        }
        if(!$this->verifyIco())
        {
            $this->outputData['message'] = 'Nesprávné IČO, nesedí kontrolní součet.';
            return false;
        }
        // kontrola v databazi        
        $itemInDB = $this->checkIcoInDB($this->getIco());
        if($itemInDB !== false && $this->dateDifference($itemInDB["last_update"],date("Y-m-d")) < 1)
        {
            $this->parseDBDetail($itemInDB);
            $this->outputData['status'] = true;
            return true;
        }
        $this->setRequestUrl($requestUrl, $this->getIco());
        if(!$this->loadXMLData()) 
        {
            $this->outputData['message'] = 'Nepodařilo se navázat spojení s databází ARES.';
            return false;
        }
        if(!$this->parseXMLDetail())
        {
            $this->outputData['message'] = 'Nenalezen žádný záznam.';
            return false;
        }
        // ulozeni do db
        if($itemInDB === false)
        {
            $this->outputData['message'] = 'Záznam byl vložen do databáze.';
            $this->insertIcoInDB();
        }
        else 
        {
            $this->outputData['message'] = 'Záznam byl aktualizován.';
            $this->updateIcoInDB();
        }
        $this->outputData['status'] = true;
        return true;
    }
    
    protected function checkIcoInDB($ico)
    {
        $sql = 'SELECT * FROM `ares` WHERE `ico` = ?';
        $q = $this->connection->queryOne($sql, array($ico));
        return $q;
    }

    protected function insertIcoInDB()
    {
        $insertData = array(
            $this->outputData['data']['ico'],
            $this->outputData['data']['dic'],
            $this->outputData['data']['firma'],
            $this->outputData['data']['ulice'],
            $this->outputData['data']['cp1'],
            $this->outputData['data']['cp2'],
            $this->outputData['data']['mesto'],
            $this->outputData['data']['psc']);
        $sql = "INSERT INTO `ares` (`ico`,`dic`,`firma`,`ulice`,`cp1`,`cp2`,`mesto`,`psc`,`last_update`) "
            . "VALUES (?,?,?,?,?,?,?,?,NOW())";
        return $this->connection->query($sql, $insertData);
    }
    
    protected function updateIcoInDB()
    {
        $updateData = array(
            $this->outputData['data']['dic'],
            $this->outputData['data']['firma'],
            $this->outputData['data']['ulice'],
            $this->outputData['data']['cp1'],
            $this->outputData['data']['cp2'],
            $this->outputData['data']['mesto'],
            $this->outputData['data']['psc'],
            $this->getIco());
        $sql = "UPDATE `ares` SET `dic`=?,`firma`=?,`ulice`=?,`cp1`=?,`cp2`=?,`mesto`=?,`psc`=?,`last_update`=NOW() "
                . "WHERE `ico` = ?";
        return $this->connection->query($sql, $updateData);
    }
    
    protected function parseDBDetail($item)
    {
        $output = array();
        $output['ico']      = $item['ico'];
        $output['dic']      = $item['dic'];
        $output['firma']    = $item['firma'];
        $output['ulice']    = $item['ulice'];
        $output['cp1']      = $item['cp1'];
        $output['cp2']      = $item['cp2'];
        $output['mesto']    = $item['mesto'];
        $output['psc']      = $item['psc'];

        $this->outputData['data'] = $output;
        return true;
    }
    
    public function getDataByName($requestUrl, $requestData)
    {
        $this->outputData['status'] = false;
        if(!$this->setName($requestData))
        {
            $this->outputData['message'] = 'Název je příliš krátký.';
            return false;
        }
        $this->setRequestUrl($requestUrl, $this->getName());
        if(!$this->loadXMLData()) 
        {
            $this->outputData['message'] = 'Nepodařilo se navázat spojení s databází ARES.';
            return false;
        }
        if(!$this->parseXMLList())
        {
            $this->outputData['message'] = 'Nenalezen žádný záznam.';
            return false;
        }
        $this->outputData['status'] = true;
        return true;
    }
    
    protected function dateDifference($date_1 , $date_2 , $differenceFormat = '%m' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat);
    }

    public function jsonResponse()
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->outputData);
        exit();
    }
}