<?php
define("M_SOF0",0xC0);
define("M_SOF1",0xC1);
define("M_SOF2",0xC2);
define("M_SOF3",0xC3);
define("M_SOF5",0xC5);
define("M_SOF6",0xC6);
define("M_SOF7",0xC7);
define("M_SOF9",0xC9);
define("M_SOF10",0xCA);
define("M_SOF11",0xCB);
define("M_SOF13",0xCD);
define("M_SOF14",0xCE);
define("M_SOF15",0xCF);
define("M_SOI",0xD8);
define("M_EOI",0xD9);
define("M_SOS",0xDA);
define("M_JFIF",0xE0);
define("M_EXIF",0xE1);
define("M_COM",0xFE);
define("NUM_FORMATS","12");
define("FMT_BYTE","1");
define("FMT_STRING","2");
define("FMT_USHORT","3");
define("FMT_ULONG","4");
define("FMT_URATIONAL","5");
define("FMT_SBYTE","6");
define("FMT_UNDEFINED","7");
define("FMT_SSHORT","8");
define("FMT_SLONG","9");
define("FMT_SRATIONAL","10");
define("FMT_SINGLE","11");
define("FMT_DOUBLE","12");
define("TAG_EXIF_OFFSET","0x8769");
define("TAG_INTEROP_OFFSET","0xa005");
define("TAG_MAKE","0x010F");
define("TAG_MODEL","0x0110");
define("TAG_ORIENTATION","0x0112");
define("TAG_EXPOSURETIME","0x829A");
define("TAG_FNUMBER","0x829D");
define("TAG_SHUTTERSPEED","0x9201");
define("TAG_APERTURE","0x9202");
define("TAG_MAXAPERTURE","0x9205");
define("TAG_FOCALLENGTH","0x920A");
define("TAG_DATETIME_ORIGINAL","0x9003");
define("TAG_USERCOMMENT","0x9286");
define("TAG_SUBJECT_DISTANCE","0x9206");
define("TAG_FLASH","0x9209");
define("TAG_FOCALPLANEXRES","0xa20E");
define("TAG_FOCALPLANEUNITS","0xa210");
define("TAG_EXIF_IMAGEWIDTH","0xA002");
define("TAG_EXIF_IMAGELENGTH","0xA003");
define("TAG_EXPOSURE_BIAS","0x9204");
define("TAG_WHITEBALANCE","0x9208");
define("TAG_METERING_MODE","0x9207");
define("TAG_EXPOSURE_PROGRAM","0x8822");
define("TAG_ISO_EQUIVALENT","0x8827");
define("TAG_COMPRESSION_LEVEL","0x9102");
define("TAG_THUMBNAIL_OFFSET","0x0201");
define("TAG_THUMBNAIL_LENGTH","0x0202");
define("PSEUDO_IMAGE_MARKER",0x123);
define("MAX_COMMENT",2000);
define("TAG_ARTIST","0x013B");
define("TAG_COPYRIGHT","0x8298");

 $FMT_BYTE_ARRAY = array();
 $FMT_STRING_ARRAY = array(
0x010E,  //Image title
0x010F, // Make - Image input equipment manufacturer
0x0110, // Model - Image input equipment model
0x0131, // Software - Software used
0x013B, // Artist - Person who created the image
0x8298,// Copyright - Copyright holder
0x9003, // DateTimeOriginal - Date and time of original data generation
);
 $FMT_USHORT_ARRAY = array(
0x0112, // Orientation
0x8822, // Exposure Program
0x9207, // Metering mode
0x9209, // Flash
0xA002, // Valid image width  PixelXDimension
0xA003, // Valid image height  PixelYDimension
);
 $FMT_ULONG_ARRAY = array(
0x0202, // JPEGInterchangeFormatLength
);
 $FMT_URATIONAL_ARRAY = array(
0x829A, // Exposure Time
0x829D, // F Number
0x9102, // CompressedBitsPerPixel
0x9202, // Aperture
0x9205, // MaxApertureValue
0x920A, // focal length
);
 $FMT_SBYTE_ARRAY = array();
 $FMT_UNDEFINED_ARRAY = array();
 $FMT_SSHORT_ARRAY = array();
 $FMT_SLONG_ARRAY = array();
 $FMT_SRATIONAL_ARRAY = array(
0x9201, // shutter speed
0x9204, // Exposure Bias
);
 $FMT_SINGLE_ARRAY = array();
 $FMT_DOUBLE_ARRAY = array();

$TagTable  = array(
  array(   0x100,   "ImageWidth"),
  array(   0x101,   "ImageLength"),
  array(   0x102,   "BitsPerSample"),
  array(   0x103,   "Compression"),
  array(   0x106,   "PhotometricInterpretation"),
  array(   0x10A,   "FillOrder"),
  array(   0x10D,   "DocumentName"),
  array(   0x10E,   "ImageDescription"),
  array(   0x10F,   "Make"),
  array(   0x110,   "Model"),
  array(   0x111,   "StripOffsets"),
  array(   0x112,   "Orientation"),
  array(   0x115,   "SamplesPerPixel"),
  array(   0x116,   "RowsPerStrip"),
  array(   0x117,   "StripByteCounts"),
  array(   0x11A,   "XResolution"),
  array(   0x11B,   "YResolution"),
  array(   0x11C,   "PlanarConfiguration"),
  array(   0x128,   "ResolutionUnit"),
  array(   0x12D,   "TransferFunction"),
  array(   0x131,   "Software"),
  array(   0x132,   "DateTime"),
  array(   0x13B,   "Artist"),
  array(   0x13E,   "WhitePoint"),
  array(   0x13F,   "PrimaryChromaticities"),
  array(   0x156,   "TransferRange"),
  array(   0x200,   "JPEGProc"),
  array(   0x201,   "ThumbnailOffset"),
  array(   0x202,   "ThumbnailLength"),
  array(   0x211,   "YCbCrCoefficients"),
  array(   0x212,   "YCbCrSubSampling"),
  array(   0x213,   "YCbCrPositioning"),
  array(   0x214,   "ReferenceBlackWhite"),
  array(   0x828D,  "CFARepeatPatternDim"),
  array(   0x828E,  "CFAPattern"),
  array(   0x828F,  "BatteryLevel"),
  array(   0x8298,  "Copyright"),
  array(   0x829A,  "ExposureTime"),
  array(   0x829D,  "FNumber"),
  array(   0x83BB,  "IPTC/NAA"),
  array(   0x8769,  "ExifOffset"),
  array(   0x8773,  "InterColorProfile"),
  array(   0x8822,  "ExposureProgram"),
  array(   0x8824,  "SpectralSensitivity"),
  array(   0x8825,  "GPSInfo"),
  array(   0x8827,  "ISOSpeedRatings"),
  array(   0x8828,  "OECF"),
  array(   0x9000,  "ExifVersion"),
  array(   0x9003,  "DateTimeOriginal"),
  array(   0x9004,  "DateTimeDigitized"),
  array(   0x9101,  "ComponentsConfiguration"),
  array(   0x9102,  "CompressedBitsPerPixel"),
  array(   0x9201,  "ShutterSpeedValue"),
  array(   0x9202,  "ApertureValue"),
  array(   0x9203,  "BrightnessValue"),
  array(   0x9204,  "ExposureBiasValue"),
  array(   0x9205,  "MaxApertureValue"),
  array(   0x9206,  "SubjectDistance"),
  array(   0x9207,  "MeteringMode"),
  array(   0x9208,  "LightSource"),
  array(   0x9209,  "Flash"),
  array(   0x920A,  "FocalLength"),
  array(   0x927C,  "MakerNote"),
  array(   0x9286,  "UserComment"),
  array(   0x9290,  "SubSecTime"),
  array(   0x9291,  "SubSecTimeOriginal"),
  array(   0x9292,  "SubSecTimeDigitized"),
  array(   0xA000,  "FlashPixVersion"),
  array(   0xA001,  "ColorSpace"),
  array(   0xA002,  "ExifImageWidth"),
  array(   0xA003,  "ExifImageLength"),
  array(   0xA005,  "InteroperabilityOffset"),
  array(   0xA20B,  "FlashEnergy"), // 0x920B in TIFF/EP
  array(   0xA20C,  "SpatialFrequencyResponse"),  // 0x920C-  -
  array(   0xA20E,  "FocalPlaneXResolution"), // 0x920E-  -
  array(   0xA20F,  "FocalPlaneYResolution"),  // 0x920F-  -
  array(   0xA210,  "FocalPlaneResolutionUnit"),  // 0x9210-  -
  array(   0xA214,  "SubjectLocation"), // 0x9214-  -
  array(   0xA215,  "ExposureIndex"),// 0x9215-  -
  array(   0xA217,  "SensingMethod"),// 0x9217-  -
  array(   0xA300,  "FileSource"),
  array(   0xA301,  "SceneType"),
  array(  0, NULL)
 ) ;

$ProcessTable = array(
array(M_SOF0,   "Baseline"),
array(M_SOF1,   "Extended sequential"),
array(M_SOF2,   "Progressive"),
array(M_SOF3,   "Lossless"),
array(M_SOF5,   "Differential sequential"),
array(M_SOF6,   "Differential progressive"),
array(M_SOF7,   "Differential lossless"),
array(M_SOF9,   "Extended sequential, arithmetic coding"),
array(M_SOF10,  "Progressive, arithmetic coding"),
array(M_SOF11,  "Lossless, arithmetic coding"),
array(M_SOF13,  "Differential sequential, arithmetic coding"),
array(M_SOF14,  "Differential progressive, arithmetic coding"),
array(M_SOF15,  "Differential lossless, arithmetic coding"),
array(0,$lang['exif_unkown'])
);

class YzuoCom_ExifInfo {

var $ImageInfo = array();
var $MotorolaOrder = 0;
var $ExifImageWidth = 0; //
var $FocalplaneXRes = 0; //
var $FocalplaneUnits = 0; //
var $sections = array();
var $currSection = 0;  /** Stores total number fo Sections */
var $BytesPerFormat = array(0,1,1,2,4,8,1,1,2,4,8,4,8);
var $DirWithThumbnailPtrs = 0;
var $ThumbnailSize = 0;
var $ReadMode = array(
"READ_EXIF" => 1,
"READ_IMAGE" => 2,
"READ_ALL" => 3
);

var $ImageReadMode = 3;
var $file =  "";
var $newFile = 1;
var $thumbnail = "";
var $thumbnailURL = "";
var $exifSection = -1;
var $errno = 0;
var $errstr = "";
var $debug = false;
var $showTags = false;
var $caching = true;
var $cacheDir = "";
function YzuoCom_ExifInfo($file = "",$lang=''){
if(!empty($file)){
$this->file = $file;
}
$this->lang=$lang;
$this->ExifImageLength   = 0;
$this->ImageInfo["CCDWidth"] = 0;
$this->ImageInfo["Distance"] = 0;
$this->ImageInfo[M_COM]  = "";
$this->ImageInfo[TAG_FLASH]  = 0;
$this->ImageInfo[TAG_MAXAPERTURE] = 0;
if(!file_exists($this->file)){
$this->errno = 1;
$this->errstr =$this->file.$this->lang['exif_filexit'];
}
$this->currSection = 0;
}

function debug($str,$TYPE = 0){
if($this->debug){
echo "<br>$str";
if($TYPE == 1){
exit;
}
}
}

function API_ProcessFile(){
if(!$this->newFile) return true;
$i = 0; $exitAll = 0;
$fp = fopen($this->file,"rb");
$this->ImageInfo["FileName"] = $this->file;
$this->ImageInfo["FileSize"] = filesize($this->file); /** Size of the File */
$this->ImageInfo["FileDateTime"] = filectime($this->file); /** File node change time */

/** check whether jped image or not */
$a = fgetc($fp);
if(ord($a) != 0xff || ord(fgetc($fp)) != M_SOI){
$this->debug($this->lang['exif_notjpg'],1);
$this->errorno = 1;
$this->errorstr = "File '".$this->file."' does not exists!";
}
$tmpTestLevel = 0;
while(!feof($fp)){
$data = array();
for ($a=0;$a<7;$a++){
$marker = fgetc($fp);
if(ord($marker) != 0xff) break;
if($a >= 6){
$this->errno = 10;
$this->errstr =$this->lang['exif_toomany_byte'];
$this->debug($this->errstr,1);
return false;
}
}

if(ord($marker) == 0xff){
$this->errno = 10;
$this->errstr = $this->lang['exif_toomany_byte'];
$this->debug($this->errstr,1);
}
$marker = ord($marker);
$this->sections[$this->currSection]["type"] = $marker;
$lh = ord(fgetc($fp));
$ll = ord(fgetc($fp));
$itemlen = ($lh << 8) | $ll;
if($itemlen < 2){
$this->errno = 11;
$this->errstr = "invalid marker";
$this->debug($this->errstr,1);
}
$this->sections[$this->currSection]["size"] = $itemlen;
$tmpDataArr = array();  /** Temporary Array */
$tmpStr = fread($fp,$itemlen-2);
$tmpDataArr[] = chr($lh);
$tmpDataArr[] = chr($ll);
$chars = preg_split('//', $tmpStr, -1, PREG_SPLIT_NO_EMPTY);
$tmpDataArr = array_merge($tmpDataArr,$chars);
$data = $tmpDataArr;
$this->sections[$this->currSection]["data"] = $data;
$this->debug("<hr><h1>".$this->currSection.":</h1>");
$this->debug("<hr>");
if(count($data) != $itemlen){
$this->errno = 12;
$this->errstr = "Premature end of file?";
$this->debug($this->errstr,1);
}

$this->currSection++; /** */

switch($marker){
case M_SOS:
$this->debug(M_SOS.$this->lang['exif_process']);;
if($this->ImageReadMode & $this->ReadMode["READ_IMAGE"]){
$cp = ftell($fp);
fseek($fp,0, SEEK_END);
$ep = ftell($fp);
fseek($fp, $cp, SEEK_SET);

$size = $ep-$cp;
$got = fread($fp, $size);

$this->sections[$this->currSection]["data"] = $got;
$this->sections[$this->currSection]["size"] = $size;
$this->sections[$this->currSection]["type"] = PSEUDO_IMAGE_MARKER;
$this->currSection++;
$HaveAll = 1;
$exitAll =1;
}
$this->debug("<br>'".M_SOS."' Section, PROCESSED<br>");
break;
case M_COM: // Comment section
$this->debug("<br>Found '".M_COM."'(Comment) Section, Processing<br>");
$this->process_COM($data, $itemlen);
$this->debug("<br>'".M_COM."'(Comment) Section, PROCESSED<br>");

$tmpTestLevel++;
break;
case M_SOI:
$this->debug($this->lang['exif_image_start']);
break;
case M_EOI:
$this->debug($this->lang['exif_image_end']);
break;
case M_JFIF:
$this->sections[--$this->currSection]["data"] = "";
break;
case M_EXIF:
$this->debug("<br>Found '".M_EXIF."'(Exif) Section, Proccessing<br>");
$this->exifSection = $this->currSection-1;
if(($this->ImageReadMode & $this->ReadMode["READ_EXIF"]) && ($data[2].$data[3].$data[4].$data[5]) == "Exif"){
$this->process_EXIF($data, $itemlen);
}else{
// Discard this section.
$this->sections[--$this->currSection]["data"] = "";
}
$this->debug("<br>'".M_EXIF."'(Exif) Section, PROCESSED<br>");
$tmpTestLevel++;
break;
case M_SOF0:
case M_SOF1:
case M_SOF2:
case M_SOF3:
case M_SOF5:
case M_SOF6:
case M_SOF7:
case M_SOF9:
case M_SOF10:
case M_SOF11:
case M_SOF13:
case M_SOF14:
case M_SOF15:
$this->debug("<br>Found M_SOFn Section, Processing<br>");
$this->process_SOFn($data,$marker);
$this->debug("<br>M_SOFn Section, PROCESSED<br>");
break;
default:
$this->debug("DEFAULT: Jpeg section marker 0x$marker x size $itemlen\n");
}
$i++;
if($exitAll == 1)  break;
if($tmpTestLevel == 2)  break;
}
fclose($fp);
$this->newFile = 0;
}


function assign($file){
if(!empty($file)){
$this->file = $file;
}
if(!file_exists($this->file)){
$this->errorno = 1;
$this->errorstr =$this->file.$this->lang['exif_filext'];
}
$this->newFile = 1;
}

function process_SOFn($data,$marker){
$data_precision = 0;
$num_components = 0;
$data_precision = ord($data[2]);
if($this->debug){
print("Image Dimension Calculation:");
print("((ord($data[3]) << 8) | ord($data[4]));");
}
$this->ImageInfo["Height"] = ((ord($data[3]) << 8) | ord($data[4]));
$this->ImageInfo["Width"] = ((ord($data[5]) << 8) | ord($data[6]));
$num_components = ord($data[7]);
if($num_components == 3){
$this->ImageInfo["IsColor"] = 1;
}else{
$this->ImageInfo["IsColor"] = 0;
}
$this->ImageInfo["Process"] = $marker;
$this->debug("JPEG image is ".$this->ImageInfo["Width"]." * ".$this->ImageInfo["Height"].", $num_components color components, $data_precision bits per sample\n");
}


function process_COM($data,$length){
if($length > MAX_COMMENT) $length = MAX_COMMENT;
$nch = 0;
for ($a=2;$a<$length;$a++){
$ch = $data[$a];
if($ch == '\r' && $data[$a+1] == '\n') continue; // Remove cr followed by lf.

$Comment .= $ch;
}
$this->ImageInfo[M_COM] = $Comment;
$this->debug("COM marker comment: $Comment\n");
}

function ProcessExifDir($DirStart, $OffsetBase, $ExifLength){
global $TagTable;

$NumDirEntries = 0;
$ValuePtr = array();

$NumDirEntries = $this->Get16u($DirStart[0],$DirStart[1]);


$this->debug("<br>Directory with $NumDirEntries entries\n");

for ($de=0;$de<$NumDirEntries;$de++){
$DirEntry = array_slice($DirStart,2+12*$de);

$Tag = $this->Get16u($DirEntry[0],$DirEntry[1]);
$Format = $this->Get16u($DirEntry[2],$DirEntry[3]);
$Components = $this->Get32u($DirEntry[4],$DirEntry[5],$DirEntry[6],$DirEntry[7]);

$ByteCount = $Components * $this->BytesPerFormat[$Format];

if($ByteCount > 4){
$OffsetVal = $this->Get32u($DirEntry[8],$DirEntry[9],$DirEntry[10],$DirEntry[11]);
if($OffsetVal+$ByteCount > $ExifLength){
$this->debug("Illegal value pointer($OffsetVal) for tag $Tag",1);
}
$ValuePtr = array_slice($OffsetBase,$OffsetVal);
}else{
$ValuePtr = array_slice($DirEntry,8);
}

if($this->showTags){
for ($a=0;;$a++){
if($TagTable[$a][0] == 0){
$this->debug("  Unknown Tag $Tag Value = ");
break;
}
if($TagTable[$a][0] == $Tag){
$this->debug("".$TagTable[$a][1]." =");
break;
}
 }

 switch($Format){
case FMT_UNDEFINED:
// Undefined is typically an ascii string.

case FMT_STRING:
// String arrays printed without function call (different from int arrays)
{
$this->debug("\"");  //"
$str ="";
for ($a=0;$a<$ByteCount;$a++){
 $str .= $ValuePtr[$a];
}
$this->debug("$str\"\n");   // "

}
break;
default:
$this->PrintFormatNumber($ValuePtr, $Format, $ByteCount);
} // end of switch
} // end of if


switch($Tag){
case TAG_MAKE:
$this->ImageInfo[TAG_MAKE]= implode("",array_slice($ValuePtr,0,$ByteCount));
break;
case TAG_MODEL:
$this->ImageInfo[TAG_MODEL] = implode("",array_slice($ValuePtr,0,$ByteCount));
break;
case TAG_DATETIME_ORIGINAL:
$this->ImageInfo[TAG_DATETIME_ORIGINAL] =  implode("",array_slice($ValuePtr,0,$ByteCount));
$this->ImageInfo["DateTime"]  = implode("",array_slice($ValuePtr,0));
break;
case TAG_USERCOMMENT:
for ($a=$ByteCount;;){
$a--;
if($ValuePtr[$a] == ' '){
//$ValuePtr[$a] = '\0';
}else{
break;
}
if($a == 0) break;
}

// Copy the comment
if(($ValuePtr[0].$ValuePtr[1].$ValuePtr[2].$ValuePtr[3].$ValuePtr[4]) == "ASCII"){
for ($a=5;$a<10;$a++){
$c = $ValuePtr[$a];
if($c != '\0' && $c != ' '){
$this->ImageInfo[TAG_USERCOMMENT]  = implode("",array_slice($ValuePtr,0,$ByteCount));
break;
}
}
}elseif(($ValuePtr[0].$ValuePtr[1].$ValuePtr[2].$ValuePtr[3].$ValuePtr[4].$ValuePtr[5].$ValuePtr[6]) == "Unicode"){
$this->ImageInfo[TAG_USERCOMMENT] = implode("",array_slice($ValuePtr,0,$ByteCount));
}else{
$this->ImageInfo[TAG_USERCOMMENT] = implode("",array_slice($ValuePtr,0,$ByteCount));
}
break;

case TAG_ARTIST:
$this->ImageInfo[TAG_ARTIST] = implode("",array_slice($ValuePtr,0,$ByteCount));
break;

case TAG_COPYRIGHT:
$this->ImageInfo[TAG_COPYRIGHT] = implode("",array_slice($ValuePtr,0,$ByteCount));
break;

case TAG_FNUMBER:
$this->ImageInfo[TAG_FNUMBER] = $this->ConvertAnyFormat(implode("",array_slice($ValuePtr,0)), $Format);
break;

case TAG_APERTURE:
case TAG_MAXAPERTURE:
if($this->ImageInfo[TAG_MAXAPERTURE] == 0){
$tmpArr =$this->ConvertAnyFormat($ValuePtr, $Format);
$this->ImageInfo[TAG_MAXAPERTURE] = exp($tmpArr[0]*log(2)*0.5);
}
break;

case TAG_FOCALLENGTH:
$this->ImageInfo[TAG_FOCALLENGTH] = $this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_SUBJECT_DISTANCE:
$this->ImageInfo["Distance"] =$this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_EXPOSURETIME:
$this->ImageInfo[TAG_EXPOSURETIME]=$this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_SHUTTERSPEED:
if($this->ImageInfo[TAG_EXPOSURETIME]==0){
$sp = $this->ConvertAnyFormat($ValuePtr,$Format);
$this->ImageInfo[TAG_SHUTTERSPEED]=(1/exp($sp[0]*log(2)));
}
break;

case TAG_FLASH:
if($this->ConvertAnyFormat($ValuePtr, $Format) & 7){
$this->ImageInfo[TAG_FLASH] = 1;
}
break;

case TAG_ORIENTATION:
$this->ImageInfo[TAG_ORIENTATION] = $this->ConvertAnyFormat($ValuePtr, $Format);
if($this->ImageInfo[TAG_ORIENTATION] < 1 || $this->ImageInfo[TAG_ORIENTATION] > 8){
$this->debug(sprintf("Undefined rotation value %d", $this->ImageInfo[TAG_ORIENTATION], 0),1);
$this->ImageInfo[TAG_ORIENTATION] = 0;
}
break;

case TAG_EXIF_IMAGELENGTH:
/**
* Image height
*/
$a = (int) $this->ConvertAnyFormat($ValuePtr, $Format);
if($this->ExifImageLength < $a) $this->ExifImageLength = $a;
$this->ImageInfo[TAG_EXIF_IMAGELENGTH] = $this->ExifImageLength;
$this->ImageInfo["Height"] = $this->ExifImageLength;
break;
case TAG_EXIF_IMAGEWIDTH:
// Use largest of height and width to deal with images that have been
// rotated to portrait format.
$a = (int) $this->ConvertAnyFormat($ValuePtr, $Format);
if($this->ExifImageWidth < $a) $this->ExifImageWidth = $a;
$this->ImageInfo[TAG_EXIF_IMAGEWIDTH] = $this->ExifImageWidth;
$this->ImageInfo["Width"] = $this->ExifImageWidth;

break;

case TAG_FOCALPLANEXRES:
$this->FocalplaneXRes = $this->ConvertAnyFormat($ValuePtr, $Format);
$this->FocalplaneXRes = $this->FocalplaneXRes[0];
$this->ImageInfo[TAG_FOCALPLANEXRES] = $this->FocalplaneXRes[0];
break;

case TAG_FOCALPLANEUNITS:
switch($this->ConvertAnyFormat($ValuePtr, $Format)){
case 1: $this->FocalplaneUnits = 25.4; break; // inch
case 2:
$this->FocalplaneUnits = 25.4;
break;
case 3: $this->FocalplaneUnits = 10;   break;  // centimeter
case 4: $this->FocalplaneUnits = 1;break;  // milimeter
case 5: $this->FocalplaneUnits = .001; break;  // micrometer
}
$this->ImageInfo[TAG_FOCALPLANEUNITS] = $this->FocalplaneUnits;
break;

case TAG_EXPOSURE_BIAS:
$this->ImageInfo[TAG_EXPOSURE_BIAS] = $this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_WHITEBALANCE:
$this->ImageInfo[TAG_WHITEBALANCE] = (int) $this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_METERING_MODE:
$this->ImageInfo[TAG_METERING_MODE] = (int) $this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_EXPOSURE_PROGRAM:
$this->ImageInfo[TAG_EXPOSURE_PROGRAM] = (int) $this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_ISO_EQUIVALENT:
$this->ImageInfo[TAG_ISO_EQUIVALENT] = (int) $this->ConvertAnyFormat($ValuePtr, $Format);
if( $this->ImageInfo[TAG_ISO_EQUIVALENT] < 50 ) $this->ImageInfo[TAG_ISO_EQUIVALENT] *= 200;
break;

case TAG_COMPRESSION_LEVEL:
$this->ImageInfo[TAG_COMPRESSION_LEVEL] = (int) $this->ConvertAnyFormat($ValuePtr, $Format);
break;

case TAG_THUMBNAIL_OFFSET:
$this->ThumbnailOffset = $this->ConvertAnyFormat($ValuePtr, $Format);
$this->DirWithThumbnailPtrs = $DirStart;
break;

case TAG_THUMBNAIL_LENGTH:
$this->ThumbnailSize = $this->ConvertAnyFormat($ValuePtr, $Format);
$this->ImageInfo[TAG_THUMBNAIL_LENGTH] = $this->ThumbnailSize;
break;

case TAG_EXIF_OFFSET:
case TAG_INTEROP_OFFSET:
{
$SubdirStart = array_slice($OffsetBase,$this->Get32u($ValuePtr[0],$ValuePtr[1],$ValuePtr[2],$ValuePtr[3]));
$this->ProcessExifDir($SubdirStart, $OffsetBase, $ExifLength);
continue;
}
}
}
{
$tmpDirStart = array_slice($DirStart,2+12*$NumDirEntries);
if(count($tmpDirStart) + 4 <= count($OffsetBase)+$ExifLength){
$Offset = $this->Get32u($tmpDirStart[0],$tmpDirStart[1],$tmpDirStart[2],$tmpDirStart[3]);
if($Offset){
$SubdirStart = array_slice($OffsetBase,$Offset);
if(count($SubdirStart) > count($OffsetBase)+$ExifLength){
if(count($SubdirStart) < count($OffsetBase)+$ExifLength+20){
}else{
$this->errno = 51;
$this->errstr = "Illegal subdirectory link";
$this->debug($this->errstr,1);
}
}else{
if(count($SubdirStart) <= count($OffsetBase)+$ExifLength){
$this->ProcessExifDir($SubdirStart, $OffsetBase, $ExifLength);
}
}
}
}else{

}
}

if(file_exists($this->thumbnail) && $this->caching && (filemtime($this->thumbnail) == filemtime($this->file) )){
$fp = fopen($this->thumbnail,"rb");
$tmpStr = fread($fp,filesize($this->thumbnail));

$this->ImageInfo["ThumbnailPointer"] = preg_split('//', $tmpStr, -1, PREG_SPLIT_NO_EMPTY);
$this->ImageInfo["ThumbnailSize"] = filesize($this->thumbnail);
} else{
if($this->ThumbnailSize && $this->ThumbnailOffset){
if($this->ThumbnailSize + $this->ThumbnailOffset <= $ExifLength){
$this->ImageInfo["ThumbnailPointer"] = array_slice($OffsetBase,$this->ThumbnailOffset);
$this->ImageInfo["ThumbnailSize"] = $this->ThumbnailSize;

}
}
}
$this->debug(sprintf($this->lang['exif_thumbnail_size'].": %d bytes\n",$this->ThumbnailSize),"TAGS");
}

function process_EXIF($data,$length){
$this->debug("Exif header $length bytes long\n");
if(($data[2].$data[3].$data[4].$data[5]) != "Exif"){
$this->errno = 52;
$this->errstr =$this->lang['exif_notformat'];
$this->debug($this->errstr,1);
}
$this->ImageInfo["FlashUsed"] = 0;
$this->FocalplaneXRes = 0;
$this->FocalplaneUnits = 0;
$this->ExifImageWidth = 0;
if(($data[8].$data[9]) == "II"){
$this->debug("Exif section in Intel order\n");
$this->MotorolaOrder = 0;
}elseif(($data[8].$data[9]) == "MM"){
$this->debug("Exif section in Motorola order\n");
$this->MotorolaOrder = 1;
}else{
$this->errno = 53;
$this->errstr = "Invalid Exif alignment marker.\n";
$this->debug($this->errstr,1);
return;
}

if($this->Get16u($data[10],$data[11]) != 0x2A || $this->Get32s($data[12],$data[13],$data[14],$data[15]) != 0x08){
$this->errno = 54;
$this->errstr = "Invalid Exif start (1)";
$this->debug($this->errstr,1);
}

$DirWithThumbnailPtrs = NULL;

$this->ProcessExifDir(array_slice($data,16),array_slice($data,8),$length);

// Compute the CCD width, in milimeters.  2
if($this->FocalplaneXRes != 0){
$this->ImageInfo["CCDWidth"] = (float)($this->ExifImageWidth * $this->FocalplaneUnits / $this->FocalplaneXRes);
}
$this->debug("Non settings part of Exif header: ".$length." bytes\n");
}
function Get16u($val,$by){
if($this->MotorolaOrder){
return ((ord($val) << 8) | ord($by));
}else{
return ((ord($by) << 8) | ord($val));
}
}

function Get32s($val1,$val2,$val3,$val4){
$val1 = ord($val1);
$val2 = ord($val2);
$val3 = ord($val3);
$val4 = ord($val4);
if($this->MotorolaOrder){
return (($val1 << 24) | ($val2 << 16) | ($val3 << 8 ) | ($val4 << 0 ));
}else{
return  (($val4 << 24) | ($val3 << 16) | ($val2 << 8 ) | ($val1 << 0 ));
}
}
function get32u($val1,$val2,$val3,$val4){
return ($this->Get32s($val1,$val2,$val3,$val4) & 0xffffffff);
}

function PrintFormatNumber($ValuePtr, $Format, $ByteCount){
switch($Format){
case FMT_SBYTE:
case FMT_BYTE:  printf("%02x\n",$ValuePtr[0]); break;
case FMT_USHORT:printf("%d\n",$this->Get16u($ValuePtr[0],$ValuePtr[1])); break;
case FMT_ULONG:
case FMT_SLONG: printf("%d\n",$this->Get32s($ValuePtr[0],$ValuePtr[1],$ValuePtr[2],$ValuePtr[3])); break;
case FMT_SSHORT:printf("%hd\n",$this->Get16u($ValuePtr[0],$ValuePtr[1])); break;
case FMT_URATIONAL:
case FMT_SRATIONAL:
printf("%d/%d\n",$this->Get32s($ValuePtr[0],$ValuePtr[1],$ValuePtr[2],$ValuePtr[3]), $this->Get32s($ValuePtr[4],$ValuePtr[5],$ValuePtr[6],$ValuePtr[7]));
break;
case FMT_SINGLE:printf("%f\n",$ValuePtr[0]);
break;
case FMT_DOUBLE:printf("%f\n",$ValuePtr[0]);
break;
default:
printf("Unknown format %d:", $Format);
}
}

function ConvertAnyFormat($ValuePtr, $Format){
$Value = 0;

switch($Format){
case FMT_SBYTE: $Value = $ValuePtr[0];  break;
case FMT_BYTE:  $Value = $ValuePtr[0];break;
case FMT_USHORT:$Value = $this->Get16u($ValuePtr[0],$ValuePtr[1]);  break;
case FMT_ULONG: $Value = $this->Get32u($ValuePtr[0],$ValuePtr[1],$ValuePtr[2],$ValuePtr[3]);  break;
case FMT_URATIONAL:
case FMT_SRATIONAL:
{
$Num = $this->Get32s($ValuePtr[0],$ValuePtr[1],$ValuePtr[2],$ValuePtr[3]);
$Den = $this->Get32s($ValuePtr[4],$ValuePtr[5],$ValuePtr[6],$ValuePtr[7]);
if($Den == 0){
$Value = 0;
}else{
$Value = (double) ($Num/$Den);
}
return array($Value,array($Num,$Den));
break;
}
case FMT_SSHORT:$Value = $this->Get16u($ValuePtr[0],$ValuePtr[1]);  break;
case FMT_SLONG: $Value = $this->Get32s($ValuePtr[0],$ValuePtr[1],$ValuePtr[2],$ValuePtr[3]);break;
case FMT_SINGLE:$Value = $ValuePtr[0];  break;
case FMT_DOUBLE:$Value = $ValuePtr[0]; break;
}
return $Value;
}

//########## 显示图片相片信息
function API_ShowExifInfo(){
global $ProcessTable;
//文件名
$exifinfo['filename']=sprintf("%s",$this->ImageInfo["FileName"]);
//文件大小
$exifinfo['filesize']=sprintf("%d",$this->ImageInfo["FileSize"]);
//浏览日期
{
$exifinfo['filetime']=sprintf("%s",date("Y-m-d H:i A",$this->ImageInfo["FileDateTime"]));
}
//制造商、相机型号
if($this->ImageInfo[TAG_MAKE]){
$exifinfo['tag_make']=sprintf("%s",$this->ImageInfo[TAG_MAKE]);
$exifinfo['tag_model']=sprintf("%s",$this->ImageInfo[TAG_MODEL]);
}
//拍摄时间
if($this->ImageInfo["DateTime"]){
$exifinfo['tag_datetime']=sprintf("%s",$this->ImageInfo[TAG_DATETIME_ORIGINAL]);
}
//分 辩 率
$exifinfo['tag_width']=sprintf("%d",$this->ImageInfo["Width"]);
$exifinfo['tag_height']=sprintf("%d",$this->ImageInfo["Height"]);
//if($this->ImageInfo[TAG_ORIENTATION]>1){
$OrientTab = array(
$this->lang['exif_undefined'],
$this->lang['exif_normal'],
$this->lang['exif_horizontal'],
$this->lang['exif_rotate_180'],
$this->lang['exif_vertical'],
$this->lang['exif_transpose'],
$this->lang['exif_rotate_90'],
$this->lang['exif_transverse'],
$this->lang['exif_rotate_270'],
);
$exifinfo['Orientation']=sprintf("%s", $OrientTab[$this->ImageInfo[TAG_ORIENTATION]]);
//}
//Color/bw
if($this->ImageInfo["IsColor"]==0){
$exifinfo['color_bw']=sprintf($this->lang['exif_whiteblack']);
}
//闪光灯
if($this->ImageInfo[TAG_FLASH]>=0){
$exifinfo['tag_flash']=sprintf("%s",$this->ImageInfo[TAG_FLASH] ? $this->lang['exif_use'] : $this->lang['exif_close']);
}
//焦距
if($this->ImageInfo[TAG_FOCALLENGTH]){
//$exifinfo['tag_focal_length']=sprintf("%4.1fmm(%s/%s)",(double)$this->ImageInfo[TAG_FOCALLENGTH][0],$this->ImageInfo[TAG_FOCALLENGTH][1][0],$this->ImageInfo[TAG_FOCALLENGTH][1][1]);
$exifinfo['tag_focal_length']=sprintf("%4.1f",(double)$this->ImageInfo[TAG_FOCALLENGTH][0]);
if($this->ImageInfo["CCDWidth"]){
$exifinfo['tag_35mm_equivalent']=sprintf("%dmm",(int)($this->ImageInfo["FocalLength"]/$this->ImageInfo["CCDWidth"]*36 + 0.5));
}
}
//CCD 宽度
if($this->ImageInfo["CCDWidth"]){
$exifinfo['tag_ccdwidth']=sprintf("%4.2fmm\n",(double)$this->ImageInfo["CCDWidth"]);
}else{
$exifinfo['tag_ccdwidth']='0.00mm';
}
//曝光时间
if($this->ImageInfo[TAG_EXPOSURETIME]){
//$exifinfo['tag_exposure_time']=sprintf("%6.3fs(%d/%d)",(double)$this->ImageInfo[TAG_EXPOSURETIME][0],$this->ImageInfo[TAG_EXPOSURETIME][1][0],$this->ImageInfo[TAG_EXPOSURETIME][1][1]);
//$exifinfo['tag_exposure_time']=sprintf("%d/%d",$this->ImageInfo[TAG_EXPOSURETIME][1][0],$this->ImageInfo[TAG_EXPOSURETIME][1][1]);
$exifinfo['tag_exposure_time']=sprintf("%d/%d",1,$this->ImageInfo[TAG_EXPOSURETIME][1][1]/$this->ImageInfo[TAG_EXPOSURETIME][1][0]);
if($this->ImageInfo[TAG_EXPOSURETIME]<=0.5){
$exifinfo['tag_exposure_time']=sprintf("1/%d",(int)(0.5+1/$this->ImageInfo[TAG_EXPOSURETIME][0]));
}
}
//F 数值
if($this->ImageInfo[TAG_FNUMBER]){
$exifinfo['tag_Fnumber']=sprintf("f/%3.1f\n",(double)$this->ImageInfo[TAG_FNUMBER][0]);
}
//Focus dist
if($this->ImageInfo["Distance"]){
if($this->ImageInfo["Distance"] < 0){
$exifinfo['Focu_dist']="Infinite";
}else{
$exifinfo['Focu_dist']=sprintf("%4.2fm",(double)$this->ImageInfo["Distance"]);
}
}
//ISO 速度
if($this->ImageInfo[TAG_ISO_EQUIVALENT]){
$exifinfo['tag_iso_exuivalent']=sprintf("%2d",(int)$this->ImageInfo[TAG_ISO_EQUIVALENT]);
}
//曝光偏差
if($this->ImageInfo[TAG_EXPOSURE_BIAS]){
//$exifinfo['tag_exposure_bias']=sprintf("%4.2f(%d/%d)",(double)$this->ImageInfo[TAG_EXPOSURE_BIAS][0],$this->ImageInfo[TAG_EXPOSURE_BIAS][1][0],$this->ImageInfo[TAG_EXPOSURE_BIAS][1][1]);
	if (in_array($this->ImageInfo[TAG_EXPOSURE_BIAS][1][0],array(0,3,6))){
		if (in_array($this->ImageInfo[TAG_EXPOSURE_BIAS][1][0],array(0))){
			$exifinfo['tag_exposure_bias']=sprintf("%d",0);
		}else{
			$exifinfo['tag_exposure_bias']=sprintf("%d",$this->ImageInfo[TAG_EXPOSURE_BIAS][1][1]/$this->ImageInfo[TAG_EXPOSURE_BIAS][1][0]);
		}
	}else{
		$exifinfo['tag_exposure_bias']=sprintf("%d/%d",$this->ImageInfo[TAG_EXPOSURE_BIAS][1][0],$this->ImageInfo[TAG_EXPOSURE_BIAS][1][1]);
	}
}
//白 平 衡
//if($this->ImageInfo[TAG_WHITEBALANCE]){
switch($this->ImageInfo[TAG_WHITEBALANCE]){
case 1:
$exifinfo['tag_white']=$this->lang['exif_tag_white1'];
break;
case 2:
$exifinfo['tag_white']=$this->lang['exif_tag_white2'];
break;
case 3:
$exifinfo['tag_white']=$this->lang['exif_tag_white3'];
break;
default:
$exifinfo['tag_white']=$this->lang['exif_tag_white4'];
}
//}
//测光方式
if($this->ImageInfo[TAG_METERING_MODE]){ // 05-jan-2001 vcs
switch($this->ImageInfo[TAG_METERING_MODE]){
case 2:
$exifinfo['tag_metering_mode']=$this->lang['exif_metering_mode1'];
break;
case 3:
$exifinfo['tag_metering_mode']=$this->lang['exif_metering_mode2'];
break;
case 5:
$exifinfo['tag_metering_mode']=$this->lang['exif_metering_mode3'];
break;
}
}
//曝光方式
//if($this->ImageInfo[TAG_EXPOSURE_PROGRAM]){ // 05-jan-2001 vcs
switch($this->ImageInfo[TAG_EXPOSURE_PROGRAM]){
case 2:
$exifinfo['tag_exposure_program']=$this->lang['exif_exposure_program1'];
break;
case 3:
$exifinfo['tag_exposure_program']=$this->lang['exif_exposure_program2'];
break;
case 4:
$exifinfo['tag_exposure_program']=$this->lang['exif_exposure_program3'];
break;
default:
$exifinfo['tag_exposure_program']=$this->lang['exif_unkown'];
break;
//}
}
//Jpeg品质
if($this->ImageInfo[TAG_COMPRESSION_LEVEL]){ // 05-jan-2001 vcs
switch($this->ImageInfo[TAG_COMPRESSION_LEVEL]){
case 1:
$exifinfo['tag_jpg_level']=$this->lang['exif_jpg_level1'];
break;
case 2:
$exifinfo['tag_jpg_level']=$this->lang['exif_jpg_level2'];
break;
case 4:
$exifinfo['tag_jpg_level']=$this->lang['exif_jpg_level3'];
break;
}
}
//Jpeg处理
for ($a=0;;$a++){
if($ProcessTable[$a][0] == $this->ImageInfo["Process"] || $ProcessTable[$a][0] == 0){
$exifinfo['tag_jpg_process']=$ProcessTable[$a][1];
break;
}
}
//Exif 注释
if($this->ImageInfo[TAG_USERCOMMENT]){
$exifinfo['tag_comment']=$this->ImageInfo[TAG_USERCOMMENT];
}
//图片注释
if($this->ImageInfo[M_COM]){
$exifinfo['tag_imgcommet']=htmlentities($this->ImageInfo[M_COM]);
}
//作者
if($this->ImageInfo[TAG_ARTIST]){
$exifinfo['tag_artist']=$this->ImageInfo[TAG_ARTIST];
}
//版权
if($this->ImageInfo[TAG_COPYRIGHT]){
$exifinfo['tag_copyright']=htmlentities($this->ImageInfo[TAG_COPYRIGHT]);
}
return $exifinfo;
}
}
?>