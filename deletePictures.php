<?php

use OxidEsales\Eshop\Core\Base;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;

include(__DIR__ . '/bootstrap.php');

/**
 * Class deletePictures
 */
class deletePictures extends Base
{
    /*DO NOT CHANGE START */
    public $sFolder = 'out/pictures/master/product/';
    public $aFiles = array();
    public $iCount = 0;

    /**
     * Title Cookie
     * @var string
     */
    public $sCookieName = 'LostPicturesAgreement';
    public $sAdminCookieSid = 'admin_sid';
    public $sOutPutMessage = '';
    public $sOutPutTitle = '';
    /*DO NOT CHANGE END */

    /*CHANGE BY USER START */

    /**
     * Max Limit, display pictures
     *
     * @var int
     */
    public $iLimitPicture    = 200;

    /**
     * Max Limit, delete Pictures
     *
     * @var int
     */
    public $iLimitPictureDelete    = 2000;
    /*CHANGE BY USER END */


    public function render()
    {
        if($this->checkForCookieAgreement())
        {
            $this->getAgreementForm();
            exit();
        }

        $sblTableExist = setupDeletePicturesTable::tableExist();

        $sblTableHasItems = false;
        $aData = $this->getLostPictureGroupByFolder();
        if(count($aData))
        {
            $sblTableHasItems = true;
        }


        $sOutput = $this->getHtmlHeader();
        $sOutput .= '
<div class="container">
    <h2>Bilder ohne Artikelzuordnung suchen und l&ouml;schen</h2>
';

        if($sblTableExist == true) {

            if(trim($this->sOutPutMessage) != '')
            {
                $sOutput .=
                    '
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">'.$this->sOutPutTitle.'</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">
                '.$this->sOutPutMessage.'
                </div>
            </div>
        </div>
    </div>
';
            }
            $sOutput.=
                '
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Statistik</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12 deletePictures">
                    <strong>eingelesene Bilder aufgeteilt auf die Ordner in '.$this->sFolder.':</strong>
                </div>            
                <div class="col-xs-12 deletePictures">
                '.$this->getLostPictureGroupByFolderAsHtml().'
                </div>
            </div>
        </div>
    </div>
';
        }

        if($sblTableExist == true) {
            $sOutput .=
                '<div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Bilder pr&uuml;fen</h3>
                </div>
            
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">                    
                            <form method="post"> 
                                <input type="hidden" name="action" value="'.$this->getFolderContents().'">
                                <input class="btn btn-sm btn-primary" type="submit" value="Bilder suchen / Verzeichnisse einlesen" title="Bilder suchen">
                            </form>
                        </div>
                    
                        <div class="col-xs-12 col-md-6">
                            Was geschieht hier:<br><br>
                            <p>In den Unterverzeichnissen in <b>'.$this->sFolder.'</b> werden die enthalten Dateien ausgelesen. 
                            Bei diesem Vorgang wird sofort gepr&uuml;ft welche Dateien <b>in der Tabelle oxarticles nicht</b> mehr enthalten sind.
                            Ist ein Bild nicht mehr an einem Artikel hinterlegt, dann erfolgt die Abspeicherung in der Tabelle d3lostpictures.
                            <br><br>
                            Die Pr&uuml;fung erfolgt pro Bildslot und <b>nicht global</b> auf alle Verzeichnisse und Bildfelder in der Tabelle oxarticles.<br>
                            Dies bedeuted:
                            z.B. die Bilder im Ordner <b>"'.$this->sFolder.'1"</b> werden nur gegen das Feld <b>oxpic1</b> gepr&uuml;ft und nicht gegen oxpic2 oder oxthumb.
                            </p>                       
                        </div>
                    </div>
                </div>
            </div>
';
        }

        if($sblTableExist == true)
        {
            $sOutput.=
                '
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Bilder anzeigen (Begrenzt auf '.$this->iLimitPicture.')</h3>
        </div>
        <div class="panel-body">
            '.$this->getLostPicturesDisplayButtonsAsHtml().'
        </div>
    </div>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Als CSV-Datei ausgeben</h3>
        </div>
        <div class="panel-body">
            '.$this->getLostPicturesAsCsvFile().'
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Bilder l&ouml;schen('.$this->iLimitPictureDelete.' Stk pro Durchgang)</h3>
        </div>
        <div class="panel-body">
            '.$this->getLostPicturesDeleteButtonsAsHtml().'
        </div>
    </div>
';

        }

        $sOutput .= '
        <hr>';
        if($sblTableExist == false) {
            $sOutput .=
                '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Datenbank</h3>
            </div>
        
            <div class="panel-body">
                <div class="col-xs-6">
                    <form method="post"> 
                        <input type="hidden" name="action" value="createTable">
                        <input class="btn btn-sm btn-primary" type="submit" value="Tabelle d3lostpictures erstellen">
                    </form>
                </div>
                <div class="col-xs-6">
                    Die Bilder ohne Zuordnung werden in dieser Tabelle d3lostpictures abgelegt.
                    <br>Im Anschluß nach dem l&ouml;schen der Bilder kann diese Tabelle und auch dieses Script mit einem Mausklick entfernt werden.
                </div>
            </div>    
        </div>
    ';
        }

        $sOutput .=
            '
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Datenbank</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                ';
        if($sblTableHasItems == true)
        {
            $sOutput .='
                <div class="col-xs-8  col-sm-6 col-md-3 dbaction"> 
                    <form method="get"> 
                        <input type="hidden" name="action" value="truncateTable">
                        <input class="btn btn-sm btn-warning" type="submit" value="Tabelle d3lostpictures leeren">
                    </form>       
                </div>';
        }

        if($sblTableExist == true) {
            $sOutput .=
                '<div class="col-xs-8 col-sm-6 col-md-3 dbaction">    
                    <form method="get"> 
                        <input type="hidden" name="action" value="dropTable">
                        <input class="btn btn-sm btn-warning" type="submit" value="Tabelle d3lostpictures l&ouml;schen">
                    </form>
                </div>';
        }

        $sOutput .=
            '<div class="col-xs-8 col-sm-3 dbaction"> 
                    <form method="get"> 
                        <input type="hidden" name="action" value="deleteScript">
                        <input class="btn btn-sm btn-danger" type="submit" value="Script vom Server l&ouml;schen">
                    </form>
                </div>   
                
                <div class="col-xs-8 col-sm-3 dbaction"> 
                    <form method="get"> 
                        <input type="hidden" name="action" value="removeAgreement">
                        <input class="btn btn-sm btn-danger" type="submit" value="Logout">
                    </form>
                </div>';

        $sOutput .=
            '    </div>
                </div>
            </div>
        ';


        $sOutput .= '<div class="panel panel-default">
                <div class="panel-heading">
                        <h3 class="panel-title">Ablauf / Legende</h3>
                </div>
                <div class="panel-body">
                    <ol>
                        <li>Datenbanktabelle d3lostpictures anlegen</li>
                        <li>Bilder einlesen (in Tabelle d3lostpictures)</li>
                        <li>Bilder l&ouml;schen (als Datei und Eintrag in d3lostpictures)</li>
                        <li>Datenbanktabelle d3lostpictures l&ouml;schen</li>
                        <li>Script vom Server entfernen</li>        
                    </ol>        
                </div>
            </div>
        </div>
       
        ';
        $sOutput.=$this->getHtmlFooter();

        echo $sOutput;
    }

    /**
     * @return string|void
     */
    public function getAgreementForm()
    {
        $oRegistry = oxNew(Registry::class);

        $sPath = $_SERVER["ORIG_PATH_INFO"]?$_SERVER["ORIG_PATH_INFO"]:$_SERVER['REQUEST_URI'];
        $sPath = basename($sPath);
        $sOutput = $this->getHtmlHeader();
        $sOutput .='                        
     <div class="container">
         <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">deletePictures - Read IT!</h3>
            </div>
        
            <div class="panel-body">
                <div class="col-xs-12">
                    <div class="bold"></div>
                
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Legen Sie ein Backup der Bilder bzw. des kompletten Shops an. -  <span class="text-info">EN: -  Make a Backup</span> </li>
                        <li class="list-group-item">Installieren Sie das Script zuerst in einem Testshop und f&uuml;hren dort einen Testlauf durch. - <span class="text-info">EN: first run in a Testshop</span></li>
                        <li class="list-group-item">Entfernen Sie das Script vom Server nach dem l&ouml;schen der Bilder. - <span class="text-info">EN: remove Script after use</span></li>
                    </ul>
                </div>
            
                <div class="form-group col-xs-12">
                    <form method="get" action="'.$oRegistry::getConfig()->getSslShopUrl().$sPath.'"> 
                        <input type="hidden" name="action" value="setAgreement">
                        <div class="checkbox">
                            <label for="agreement"><input name="agreement" id="agreement" type="checkbox" value="true">Ich habe die oben genannten Punkte gelesen und f&uuml;hre das Script auf eigene Verantwortung aus.</label>
                            - <span class="text-info">EN: I read it and use it on my own risk.</span>                            
                        </div>
                        <p>
                            <input class="btn btn-sm btn-primary" type="submit" value="Login">
                        </p>
                    </form>
                </div>            
            </div>    
        </div>
    </div>
';

        $sOutput .= $this->getHtmlFooter();

        echo $sOutput;
    }

    /**
     * @return bool
     */
    public function checkForCookieAgreement()
    {
        if(isset($_COOKIE[$this->sCookieName]) && $_COOKIE[$this->sCookieName] == true
            #&&
            #isset($_COOKIE[$this->sAdminCookieSid]) && $_COOKIE[$this->sAdminCookieSid] != ''
        )
        {
            return false;
        }
        return true;
    }

    public function setAgreement()
    {
        $oRegistry = oxNew(Registry::class);

        $sPath = $_SERVER["ORIG_PATH_INFO"]?$_SERVER["ORIG_PATH_INFO"]:$_SERVER['REQUEST_URI'];
        $sPath = basename($sPath);

        //action=setAgreement - entfernen
        $aUrl = parse_url($oRegistry::getConfig()->getSslShopUrl().$sPath);
        $sPath = ltrim($aUrl['path'],'/');

        if($oRegistry::getConfig()->getRequestParameter('agreement') == true)
        {
            setcookie($this->sCookieName, true, strtotime( '+7 days' ));
        }
        $oRegistry::getUtils()->redirect($oRegistry::getConfig()->getSslShopUrl().$sPath,false);
    }

    public function removeAgreement()
    {
        $oRegistry = oxNew(Registry::class);

        $sPath = $_SERVER["ORIG_PATH_INFO"]?$_SERVER["ORIG_PATH_INFO"]:$_SERVER['REQUEST_URI'];
        $sPath = basename($sPath);

        //action=removeAgreement - entfernen
        $aUrl = parse_url($oRegistry::getConfig()->getSslShopUrl().$sPath);
        $sPath = ltrim($aUrl['path'],'/');

        setcookie($this->sCookieName, false, time()-1000);
        $oRegistry::getUtils()->redirect($oRegistry::getConfig()->getSslShopUrl().$sPath,false);
    }

    /**
     * @return string
     */
    public function getHtmlFooter()
    {
        return '
            </body>
        </html>';
    }

    /**
     * @return string
     */
    public function getHtmlHeader()
    {
        return '<!DOCTYPE html>
<html lang="de" >
<head>
<title>deletePictures - nicht mehr genutzte Bilder l&ouml;schen</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<!-- Das neueste kompilierte und minimierte CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

<!-- Optionales Theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">



<style>
    td {
        vertical-align: top;
        border-top: aliceblue solid;
    }    
    
    .shop td{
        border-top: none;
    }
    tr:nth-of-type(even){
        //background-color: aliceblue;
    }
    
    .shop table:first-child{
        border-bottom: aliceblue solid;
    }
    
    table.LostFilesInDatabas td{border-top:1px solid; border-right:1px solid; min-width:50px; margin: 0;}
   
    .deletePictures,
    .displayPictures,
    .dbaction
    {
        margin-bottom: 10px;
    }
   
   </style>
</head>
<body>

<!-- Das neueste kompilierte und minimierte JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
';
    }

    /**
     * todo: Pr&uuml;fung af unvollst&auml;ndig eingelesenen Ordner
     */
    public function getFolderContents()
    {

        //Tabelle leeren
        //$this->truncateTable();
        $aSlotsFromLostPictures = $this->getLostPicturesSlotsFromTable();

        $sFolder = $this->getPathToShop().$this->sFolder;

        $aFolder = $this->getFolders();

        natsort($aFolder);

        foreach ($aFolder as $sTmpFolder)
        {
            $this->sOutPutTitle = 'Ordner einlesen';
            //echo "<br>Folder:".$sTmpFolder;
            if($this->hasFinishedImport($sTmpFolder) == false)
            {
                //Eintr&auml;ge pro Slot loeschen
                $this->truncateTable($sTmpFolder);
            }

            if(key_exists($sTmpFolder,$aSlotsFromLostPictures))
            {
                continue;
            }
            $this->sOutPutMessage.= "<br>Pr&uuml;fe Ordner: ".$sFolder.$sTmpFolder;

            if ($handle = opendir($sFolder.$sTmpFolder))
            {
                $aFilesOxPics = $this->getAllPicturesFromTable($sTmpFolder);

                $this->setStartStopFlag($sTmpFolder,'START');

                while (false !== ($entry = readdir($handle)))
                {
                    if ($entry == "." || $entry == ".." || $entry == "dir.txt" || trim($entry) == '')
                    {
                        continue;
                    }
                    if(isset($aFilesOxPics[$entry]))
                    {
                        continue;
                    }

                    $aPictureData = array();

                    $aTmpImagePro = getimagesize ($sFolder.$sTmpFolder."/".$entry);

                    $aPictureData['D3FOLDER'] = $sTmpFolder;
                    $aPictureData['D3WIDTH'] = $aTmpImagePro[0];
                    $aPictureData['D3HEIGHT'] = $aTmpImagePro[1];
                    $aPictureData['D3IMAGETYPE'] = $aTmpImagePro['mime'];
                    $aPictureData['D3LASTCHANGE'] = date ("Y-m-d H:i:s",filemtime($sFolder.$sTmpFolder."/".$entry)) ;
                    $aPictureData['D3FILESIZE'] = filesize ($sFolder.$sTmpFolder."/".$entry) ;
                    $aPictureData['D3FILETYPE'] = 'product';
                    $aPictureData['D3FILENAME'] = $entry;
                    $aPictureData['D3DATE'] = date ("Y-m-d H:i:s");
                    $aPictureData['OXID'] = md5($entry.$sTmpFolder.date ("Y-m-d H:i:s"));

                    $oItem = oxNew(BaseModel::class);
                    $oItem->init("d3lostpictures");
                    $oItem->assign($aPictureData);

                    $oItem->save();

                }
                //die();
                $this->setStartStopFlag($sTmpFolder,'STOP');


                //Flags entfernen
                $this->deleteStartStopFlag($sTmpFolder);
                closedir($handle);
            }
            else{
                $this->sOutPutMessage.= "<br>Konnte Ordner nicht oeffnen:".$sFolder.$sTmpFolder;
            }
            $this->iCount = 0;
        }
    }

    /**
     * @return array
     */
    public function getLostPicturesSlotsFromTable()
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
select d3folder from d3lostpictures
where 1
group by d3folder
MYSQL;
        $sRs = $oDb->getAll($sQuery);

        $aTmp = array();
        foreach($sRs as $aSlot)
        {
            $aTmp[$aSlot['d3folder']] = $aSlot['d3folder'];
        }

        return $aTmp;
    }

    /**
     * @return array
     */
    public function getFolders()
    {
        $sFolder = $this->getPathToShop() . $this->sFolder;
        $aFolders = array();

        if ($handle = opendir($sFolder)) {
            while (false !== ($entry = readdir($handle))) {
                $this->iCount++;
                if ($entry != "." && $entry != "..") {

                    if(is_dir($sFolder.$entry))
                    {
                        $aFolders[] = $entry;
                    }
                }
            }
        }

        return $aFolders;
    }

    /**
     * @param $sSlot
     *
     * @return array
     */
    public function getAllPicturesFromTable($sSlot)
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        //echo $sSlot;
        $sPictureDbField = 'oxpic'.$sSlot;
        if($sSlot == 'thumb' || $sSlot == 'icon'){
            $sPictureDbField = 'ox'.$sSlot;
        }

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
select {$sPictureDbField} from oxarticles where {$sPictureDbField} != ''
order by {$sPictureDbField}
MYSQL;
        $aDbResult =  $oDb->getAll($sQuery);
        $aTmp = array();
        foreach($aDbResult as $aPicture)
        {
            $aTmp[$aPicture[$sPictureDbField]] = $aPicture[$sPictureDbField];
        }

        return $aTmp;
    }

    /**
     * @return string
     */
    public function getPathToShop()
    {
        $oRegistry = oxNew(Registry::class);

        return $oRegistry::getConfig()->getConfigParam('sShopDir');
    }


    /**
    select * from
    d3lostpictures where
    d3folder = '1'
    AND
    d3filename not in(
    select oxpic1 from oxarticles
    where oxpic1 != ''
    );

    select * from
    d3lostpictures where
    d3folder = '2'
    AND
    d3filename not in(
    select oxpic2 from oxarticles
    where oxpic2 != ''
    );
     */

    /**
     * @param $sSlot
     */
    public function getLostPicturesSlot($sSlot)
    {
        $this->sOutPutMessage.=  $this->getLostPictureAsTableContent($this->getLostPictures($sSlot));
    }

    /**
     * @param $aPictures
     * @param $sSlot
     *
     * @return string
     */
    public function getLostPictureAsTableContent($aPictures)
    {
        $oRegistry = oxNew(Registry::class);

        $this->sOutPutTitle = 'Anzeige Bilder';

        //dumpvar($aPictures);
        $sUrlToFolder = $oRegistry::getConfig()->getShopUrl().$this->sFolder;

        $sContent = '<table class="table table-striped table-hover table-sm">
        <tr>
            <th>Bild</th>
            <th>Pfad + Bildname</th>
            <th>Breite * H&ouml;he</th>
            <th>Dateigr&ouml;sse</th>
            <th>letzte &Auml;ndeurung</th>                    
        </tr>';
        foreach ($aPictures as $aPicture)
        {
            //$sFileName = $aPicture['D3FILENAME'].'.'.$aPicture['D3FILETYPE'];
            $sFileName = $aPicture['D3FILENAME'];
            $sPathWithPicture = $sUrlToFolder.$aPicture['D3FOLDER'].'/'.$sFileName;

            $sContent .= '
            <tr>
            <td><a href="'.$sPathWithPicture.'" target="_blank"><img src="'.$sPathWithPicture.'" style="max-height: 100px" alt="'.$sPathWithPicture.'"></a></td>
            <td>'.$this->sFolder.$aPicture['D3FOLDER'].'/'.$sFileName.'</td>
            <td>'.$aPicture['D3WIDTH'].'px * '.$aPicture['D3HEIGHT'].'px</td>
            <td>'.$this->formatBytes($aPicture['D3FILESIZE']).'</td>
            <td>'.$aPicture['D3LASTCHANGE'].'</td>
            </tr>
            ';
        }
        $sContent .= '</table>';

        return $sContent;
    }

    /**
     * @param $sSlot
     */
    public function getLostPicturesInCsvFile($sSlot)
    {
        $oRegistry = oxNew(Registry::class);

        //$sUrlToFolder = oxRegistry::getConfig()->getShopUrl().$this->sFolder;
        $sSeparator = '"';
        $sColumn = ";".PHP_EOL;

        $sSeparator = '';
        $sColumn = PHP_EOL;

        $sFileContent = '';
        foreach ($this->getLostPictures($sSlot) as $aPicture)
        {
            $sFileName = $aPicture['D3FILENAME'];
            $sFileContent .= $sSeparator.$this->sFolder.$aPicture['D3FOLDER'].'/'.$sFileName.$sSeparator.$sColumn;
        }

        $oUtils = $oRegistry::getUtils();
        $sFilename = 'Folder_'.$sSlot.'.csv';
        ob_start();
        //$sPDF = ob_get_contents();
        $sCsv = $sFileContent;
        ob_end_clean();
        $oUtils->setHeader("Pragma: public");
        $oUtils->setHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        $oUtils->setHeader("Expires: 0");
        $oUtils->setHeader("Content-type: application/csv");
        $oUtils->setHeader("Content-Disposition: attachment; filename=" . $sFilename);
        $oRegistry::getUtils()->showMessageAndExit($sCsv);

    }

    /**
     * @param $sSlot
     *
     * @return mixed
     */
    public function getLostPictures($sSlot)
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);

        /*
        $sPictureDbField = 'oxpic'.$sSlot;
        if($sSlot == 'thumb' || $sSlot == 'icon'){
            $sPictureDbField = 'ox'.$sSlot;
        }*/

        $iCount = $this->getCountLostPictures($sSlot);

        $sLimit  = '';
        if($iCount > $this->iLimitPicture)
        {
            $sLimit = ' LIMIT '.$this->iLimitPicture;
        }
        $sQuery =
            <<<MYSQL
    SELECT * FROM
    d3lostpictures WHERE
    d3folder = {$oDb->quote($sSlot)}
    AND D3FILENAME NOT IN('START', 'STOP')
    ORDER BY D3lASTCHANGE    
{$sLimit}
MYSQL;

        //echo $sQuery;
        //die();
        $res = $oDb->getAll($sQuery);
        return $res;
    }

    /**
     * @param $sSlot
     *
     * @return false|string
     */
    public function getCountLostPictures($sSlot)
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
select count(oxid) as files from d3lostpictures
where d3folder = '{$sSlot}'
AND D3FILENAME NOT IN('START', 'STOP')
MYSQL;

        return $oDb->getOne($sQuery);
    }

    /**
     * @return array
     */
    public function getLostPictureGroupByFolder()
    {
        if($this->tableExist() == false)
        {
            return array();
        }

        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
select d3folder, count(oxid) as files, sum(d3filesize) as size from d3lostpictures
where 1
group by d3folder
order by LENGTH(cast(d3folder as CHAR)),d3folder  
MYSQL;
        $aRes = $oDb->getAll($sQuery);

        $aTmp = array();

        foreach ($aRes as $aFolder)
        {
            $aTmp[$aFolder['d3folder']] = $aFolder;
        }
        return $aTmp;
    }

    /**
     * @param $sSlot
     *
     * @return false|string
     */
    public function getCountLostPictureGroupByFolder($sSlot)
    {
        $sPictureDbField = 'oxpic'.$sSlot;
        if($sSlot == 'thumb' || $sSlot == 'icon'){
            $sPictureDbField = 'ox'.$sSlot;
        }

        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
select count(oxid) from
    d3lostpictures where
    d3folder = '{$sSlot}'
    AND
    d3filename not in(
        select {$sPictureDbField} from oxarticles
        where {$sPictureDbField} != ''
    );
MYSQL;
        //echo $sQuery;
        //die();
        return $oDb->getOne($sQuery);
    }

    /**
     * @return string
     */
    public function getLostPictureGroupByFolderAsHtml()
    {
        $aData = $this->getLostPictureGroupByFolder();
        $aDataUnFinishedFolders = $this->checkAllSlotsIfFinished();

        if(count($aData) == 0)
        {
            $sHtml=
                '            
                <strong>Keine Bilder gefunden oder eingelesen.</strong>
                
            ';
            return $sHtml;
        }

        //dumpvar($aData);
        $sHtml= '<table class="LostFilesInDatabas_ table table-striped table-hover table-sm">
        <tr>
        <th>Slot/Ordner: </th>';
        foreach ($aData as $aFiles)
        {
            $sHtml.='<th>'.$aFiles['d3folder'].'</th>';
        }
        $sHtml.= '</tr>
        <tr>
        
        <td><strong>Anzahl Dateien: </strong></td>';
        foreach ($aData as $aFiles)
        {
            $sHtml.='<td>'.$aFiles['files'].'</td>';
        }
        $sHtml.= '</tr>

        <tr>
        
        <td><strong>Speicherplatz:</strong> </td>';
        foreach ($aData as $aFiles)
        {
            $sHtml.='<td>'.$this->formatBytes($aFiles['size']).'</td>';
        }
        $sHtml.= '</tr>

        <tr>
        <td><strong>Status Ordner:</strong> </td>';
        foreach ($aData as $sKey => $aFiles)
        {
            $sHtml.='<td>';
            if(isset($aDataUnFinishedFolders[$sKey])){
                $sHtml.= 'Abbruch';
            }
            else {
                $sHtml.= 'OK';
            }
            $sHtml.='</td>';
        }
        $sHtml.= '</tr>';

        $sHtml.= '</table>';

        return $sHtml;
    }

    /**
     *
     * https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
     * @param     $size
     * @param int $precision
     *
     * @return string
     */
    public function formatBytes($size)
    {
        $mod = 1024;
        $units = explode(' ','B KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * @return string
     */
    public function getLostPicturesDeleteButtonsAsHtml()
    {
        $aData = $this->getLostPictureGroupByFolder();

        if(count($aData) == 0)
        {
            $sHtml=
                '
<div class="row">
<div class="col-xs-12 deletePictures">
<strong>Keine Bilder gefunden oder eingelesen.</strong>
</div>
</div>
';
            return $sHtml;
        }

        $sHtml = '<div class="row">';

        //dumpvar($aData);
        foreach ($aData as $aSlot)
        {
            $sSlot = $aSlot['d3folder'];
            if($aSlot['d3folder'] == 'icon' && $aSlot['d3folder'] == 'thumb')
            {
                $sSlot = 'Pic'.$aSlot['d3folder'];
            }

            $sHtml.='
                <div class="col-12 col-sm-6 col-md-3 deletePictures">
                    <form method="post">
                        <input type="hidden" name="action" value="deleteLostPictures">
                        <input type="hidden" name="parameter" value="'.$sSlot.'">
                        <input class="btn btn-sm btn-danger" type="submit" value="Ordner '.$sSlot.' - l&ouml;schen">
                    </form>
                </div>'
            ;
        }
        $sHtml.= '</div>';
        return $sHtml;
    }
    /**
     * @return string
     */
    public function getLostPicturesAsCsvFile()
    {
        $aData = $this->getLostPictureGroupByFolder();

        if(count($aData) == 0)
        {
            $sHtml=
                '
<div class="row">
<div class="col-xs-12 deletePictures">
<strong>Keine Bilder gefunden oder eingelesen.</strong>
</div>
</div>
';
            return $sHtml;
        }

        $sHtml = '<div class="row">';

        foreach ($aData as $aSlot)
        {
            $sSlot = $aSlot['d3folder'];
            if($aSlot['d3folder'] == 'icon' && $aSlot['d3folder'] == 'thumb')
            {
                $sSlot = 'Pic'.$aSlot['d3folder'];
            }

            $sHtml.='
                <div class="col-12 col-sm-6 col-md-3 deletePictures">
                    <form method="post">
                        <input type="hidden" name="action" value="getLostPicturesInCsvFile">
                        <input type="hidden" name="parameter" value="'.$sSlot.'">
                        <input class="btn btn-sm btn-primary" type="submit" value="Ordner '.$sSlot.'">
                    </form>
                </div>'
            ;
        }
        $sHtml.= '</div>';
        return $sHtml;
    }

    /**
     * @return string
     */
    public function getLostPicturesDisplayButtonsAsHtml()
    {
        $aData = $this->getLostPictureGroupByFolder();

        if(count($aData) == 0)
        {
            $sHtml=
                '<div class="row">
                <div class="col-xs-12 deletePictures">
                    <p><strong>Keine Bilder gefunden oder eingelesen.</strong></p>
                </div>
            </div>
';
            return $sHtml;
        }

        $sHtml =
            '
        <div class="row">
';

        foreach ($aData as $aSlot)
        {
            //$iCount = $this->getCountLostPictures($aSlot['d3folder']);

            $sSlot = $aSlot['d3folder'];
            if($aSlot['d3folder'] == 'icon' && $aSlot['d3folder'] == 'thumb')
            {
                $sSlot = 'Pic'.$aSlot['d3folder'];
            }

            $sHtml .=
                '<div class="col-1 col-sm-6 col-md-3 displayPictures '.$sSlot.'">
<!--
                    <form method="get"> 
                        <input type="hidden" name="action" value="getLostPicturesSlot">
                        <input type="hidden" name="parameter" value="'.$sSlot.'">
                        <input class="btn btn-sm btn-info" type="submit" value="Slot '.$sSlot.' - anzeigen">
                    </form>
                    -->
                    <a class="btn btn-info"  href="?action=getLostPicturesSlot&parameter='.$sSlot.'">Ordner '.$sSlot.' - anzeigen</a>
                </div>
';

        }

        $sHtml.=
            '
                <div class="col-sm-12">Ausgabe der Bilder in einer tabelarischen Form.</div>
            </div>            
';

        return $sHtml;
    }

    /**
     * deletePictures constructor.
     *
     * @param $sSlot
     *
     * @return string
     */
    public function deleteLostPictures($sSlot)
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
        SELECT oxid, D3FILENAME as File
        FROM d3lostpictures
        WHERE D3FOLDER = {$oDb->quote($sSlot)}
        LIMIT {$this->iLimitPictureDelete};
MYSQL;

        $sRes = $oDb->getAll($sQuery);
        $sMessage = '';

        $this->sOutPutTitle = 'gel&ouml;schte Bilder';

        foreach($sRes As $aFile)
        {
            $blDelete = $this->_removePictureFromFolder($aFile['File'],$sSlot);
            if($blDelete == true) {
                $sQueryDelete =
                    <<<MYSQL
            DELETE FROM d3lostpictures
WHERE oxid = '{$aFile['oxid']}'
MYSQL;

                $oDb->execute($sQueryDelete);
            }
        }

        return $sMessage;
    }

    /**
     * @param $sSlot
     * @param $sType
     */
    public function setStartStopFlag($sSlot,$sType)
    {
        $aPictureData = array();
        $aPictureData['D3FOLDER'] = $sSlot;
        $aPictureData['D3FILETYPE'] = '';
        $aPictureData['D3WIDTH'] = '';
        $aPictureData['D3HEIGHT'] = '';
        $aPictureData['D3IMAGETYPE'] = '';
        $aPictureData['D3LASTCHANGE'] = date ("Y-m-d H:i:s") ;
        $aPictureData['D3FILESIZE'] = 0;
        $aPictureData['D3FILETYPE'] = '';
        $aPictureData['D3FILENAME'] = $sType;
        $aPictureData['D3DATE'] = date ("Y-m-d H:i:s");
        $aPictureData['OXID'] = md5($sType.$sSlot.date ("Y-m-d H:i:s"));

        $oItem = oxNew(BaseModel::class);
        $oItem->init("d3lostpictures");
        $oItem->assign($aPictureData);

        $oItem->save();
    }

    /**
     * @param $sSlot
     *
     * @return object
     */
    public function deleteStartStopFlag($sSlot)
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
        DELETE FROM `d3lostpictures`
        WHERE D3FOLDER = '{$sSlot}'
        AND D3FILENAME in('START', 'STOP');
MYSQL;
        return $oDb->execute($sQuery);
    }

    /**
     * @param $sSlot
     *
     * @return false|string
     */
    public function hasFinishedImport($sSlot)
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
        SELECT count(oxid) as countflag FROM `d3lostpictures`
        WHERE D3FOLDER = '{$sSlot}'
        AND D3FILENAME in('START')
        AND D3FILENAME NOT in('STOP');
MYSQL;
        $iRes = $oDb->getOne($sQuery);

        if($iRes == 0 || $iRes == 2)
        {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function checkAllSlotsIfFinished()
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
       SELECT d3folder, count(oxid) as countflag FROM `d3lostpictures`
    WHERE D3FILENAME in('START')
    AND D3FILENAME NOT in('STOP')
    GROUP BY d3folder
    ORDER by LENGTH(cast(d3folder as CHAR)),d3folder
MYSQL;
        $aRes = $oDb->getAll($sQuery);

        $aTmp = array();

        foreach ($aRes as $aFolder)
        {
            $aTmp[$aFolder['d3folder']] = $aFolder;
        }
        return $aTmp;
    }

    /**
     * @param $sFilePath
     * @param $sSlot
     *
     * @return bool
     */
    protected function _removePictureFromFolder($sFilePath,$sSlot)
    {
        $sPathToFile = $this->getPathToShop().$this->sFolder.$sSlot.'/'.$sFilePath;
        $this->sOutPutMessage.= "<br>".$this->sFolder.$sSlot.'/'.$sFilePath;

        if(file_exists($sPathToFile) == false)
        {
            $this->sOutPutMessage.= ' - File not found';
            return false;
        }

        if(unlink($sPathToFile) == false)
        {
            $this->sOutPutMessage.= ' - File could not delete';
            return false;
        }

        return true;
    }

    public function createTable()
    {
        return setupDeletePicturesTable::createTable();
    }

    public function dropTable()
    {
        if($this->tableExist() == true) {
            return setupDeletePicturesTable::dropTable();
        }

        return false;
    }

    /**
     * @param $sSlot
     *
     * @return bool|void
     */
    public function truncateTable($sSlot)
    {
        if($this->tableExist() == true) {
            setupDeletePicturesTable::truncateTable($sSlot);
        }
    }

    /**
     * @return bool
     */
    public function tableExist()
    {
        return setupDeletePicturesTable::tableExist();
    }

    public function deleteScript()
    {
        unlink($_SERVER['SCRIPT_FILENAME']);

        if (is_file($_SERVER['SCRIPT_FILENAME'])) {
            exit('Script konnte nicht gel&ouml;scht werden.');
        } else {
            exit('Script wurde gel&ouml;scht');
        }
    }

}

/**
 * Class setupDeletePicturesTable
 */
class setupDeletePicturesTable extends Base
{
    public $_sTable = 'd3lostpictures';

    /**
     * @throws oxConnectionException
     */
    public static function createTable()
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);

        $sQuery =
            <<<MYSQL
CREATE TABLE IF NOT EXISTS `d3lostpictures` (
	`OXID` CHAR(32) NOT NULL COMMENT 'Hashwert',
	`D3FOLDER` VARCHAR(50) NOT NULL COMMENT 'pic11, icon',
	`D3FILENAME` VARCHAR(255) NOT NULL,
	`D3FILETYPE` VARCHAR(10) NOT NULL COMMENT 'product, category, vendor',
	`D3WIDTH` INT(11) NOT NULL,
	`D3HEIGHT` INT(11) NOT NULL,
	`D3FILESIZE` INT(11) NOT NULL,
	`D3LASTCHANGE` DATETIME NOT NULL,
	`D3IMAGETYPE` VARCHAR(32) NOT NULL,
	`D3DATE` DATETIME NOT NULL,
	PRIMARY KEY (`OXID`),
	INDEX `D3FOLDER` (`D3FOLDER`),
	INDEX `D3FILENAME` (`D3FILENAME`)
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;

MYSQL;

        $oDb->execute($sQuery);
    }

    /**
     * @throws oxConnectionException
     */
    public static function dropTable()
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
Drop table `d3lostpictures`;
MYSQL;

        $oDb->execute($sQuery);
    }

    /**
     * @param $sSlot
     *
     * @throws oxConnectionException
     */
    public static function truncateTable($sSlot)
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery =
            <<<MYSQL
TRUNCATE `d3lostpictures`;
MYSQL;

        if(trim($sSlot) != '')
        {
            $sQuery =
                <<<MYSQL
DELETE FROM `d3lostpictures` WHERE
D3FOLDER = '{$sSlot}';
MYSQL;
        }

        $oDb->execute($sQuery);
    }

    /**
     * @return bool
     * @throws oxConnectionException
     */
    public static function tableExist()
    {
        $oDatabaseProvider = oxNew(DatabaseProvider::class);

        $oDb = $oDatabaseProvider::getDb($oDatabaseProvider::FETCH_MODE_ASSOC);
        $sQuery = <<<MYSQL
SHOW TABLES LIKE 'd3lostpictures';
MYSQL;
        return (bool)$oDb->getOne($sQuery);
    }
}

/** @var deletePictures $oBilderLesen */
$oDeletePictures = oxNew('deletePictures');

$oRegistry = oxNew(Registry::class);

$sAction = $oRegistry::getConfig()->getRequestParameter('action');
$sParameter = $oRegistry::getConfig()->getRequestParameter('parameter');
if(trim($sAction) != '')
{
    $oDeletePictures->{$sAction}($sParameter);
}


$oDeletePictures->render();