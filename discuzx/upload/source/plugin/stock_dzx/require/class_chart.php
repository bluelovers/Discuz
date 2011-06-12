<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-04-25
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class pChart
{
	private $Palette = array(
			"0"=>array("R"=>188,"G"=>224,"B"=>46),	//BCE02E
			"1"=>array("R"=>224,"G"=>100,"B"=>46),	//E0642E
			"2"=>array("R"=>224,"G"=>214,"B"=>46),	//E0D62E
			"3"=>array("R"=>46,"G"=>151,"B"=>224),	//2E97E0
			"4"=>array("R"=>176,"G"=>46,"B"=>224),	//B02EE0
			"5"=>array("R"=>224,"G"=>46,"B"=>117),	//E02E75
			"6"=>array("R"=>92,"G"=>224,"B"=>46),	//5CE02E
			"7"=>array("R"=>224,"G"=>176,"B"=>46)	//E0B02E
		);
	private $XSize		= NULL;
	private $YSize		= NULL;
	private $Picture	= NULL;
	
	private $GArea_X1		= NULL;
	private $GArea_Y1		= NULL;
	private $GArea_X2		= NULL;
	private $GArea_Y2		= NULL;
	private $GAreaXOffset	= NULL;
	private $VMax			= NULL;
	private $VMin			= NULL;
	private $Divisions		= NULL;
	private $DivisionHeight	= NULL;
	private $DivisionCount	= NULL;
	private $DivisionRatio	= NULL;
	private $DivisionWidth	= NULL;
	private $DataCount		= NULL;

	private $FontName		= NULL;
	private $FontSize		= NULL;

	private $LineWidth      = 1;
	private $LineDotSize    = 0;
	private $Layers			= NULL;
	private $AntialiasQuality = 20;	//设置线条质量 : 0 最大, 100 最小
	public function __construct($XSize,$YSize)
	{
		$this->XSize	= $XSize;
		$this->YSize	= $YSize;
		$this->Picture	= imagecreatetruecolor($XSize,$YSize);
		$C_White = imagecolorallocate($this->Picture,255,255,255);
		imagefilledrectangle($this->Picture,0,0,$XSize,$YSize,$C_White);
		imagecolortransparent($this->Picture,$C_White);
	}
	public function setFontProperties($FontName,$FontSize)
	{
		$this->FontName = $FontName;
		$this->FontSize = $FontSize;
	}
	public function setGraphArea($X1,$Y1,$X2,$Y2)
	{
		$this->GArea_X1 = $X1;
		$this->GArea_Y1 = $Y1;
		$this->GArea_X2 = $X2;
		$this->GArea_Y2 = $Y2;
	}
	public function setFixedScale($VMin,$VMax,$Divisions=5)
	{
		$this->VMin = $VMin;
		$this->VMax = $VMax;
		$this->Divisions = $Divisions;
	}
	public function drawGraphArea($R,$G,$B)
	{
		$this->drawRectangle($this->GArea_X1,$this->GArea_Y1,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B);
	}
	public function drawScale(&$Data,&$Datacomintrotion,$R,$G,$B,$Decimals=1)
	{
		$C_TextColor = imagecolorallocate($this->Picture,$R,$G,$B);
		$this->drawLine($this->GArea_X1,$this->GArea_Y1,$this->GArea_X1,$this->GArea_Y2,$R,$G,$B);
		$this->drawLine($this->GArea_X1,$this->GArea_Y2,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B);
		if ( $this->VMin == NULL && $this->VMax == NULL )
		{
			if ( isset($Datacomintrotion['Values'][0]) )
			{
				$this->VMin = $Data[0][$Datacomintrotion['Values'][0]];
				$this->VMax = $Data[0][$Datacomintrotion['Values'][0]];
			}
			else
			{
				$this->VMin = 2147483647;
				$this->VMax = -2147483647;
			}
			$this->VMin = 0;//这里强制最小值从0开始，股价不可能为负数
			foreach( $Data as $Key => $Values )
			{
				foreach( $Datacomintrotion['Values'] as $Key2 => $ColName )
				{
					if ( isset($Data[$Key][$ColName]) )
					{
						$Value = $Data[$Key][$ColName];
						if ( is_numeric($Value) )
						{
							if ( $this->VMax < $Value )
							{
								$this->VMax = $Value;
							}
							if ( $this->VMin > $Value )
							{
								$this->VMin = $Value;
							}
						}
					}
				}
			}
			$DataRange = $this->VMax - $this->VMin;
			if ( $DataRange == 0 )
			{
				$DataRange = .1;
			}
			$ScaleOk = FALSE;
			$Factor = 1;
			$MinDivHeight = 25;
			$MaxDivs = ($this->GArea_Y2 - $this->GArea_Y1) / $MinDivHeight;
			if ( $MaxDivs > 1 )
			{
				while ( !$ScaleOk )
				{
					$Scale1 = ( $this->VMax - $this->VMin ) / $Factor;
					$Scale2 = ( $this->VMax - $this->VMin ) / $Factor / 2;
					if ( $Scale1 > 1 && $Scale1 <= $MaxDivs && !$ScaleOk )
					{
						$ScaleOk = TRUE;
						$Divisions = floor($Scale1);
						$Scale = 1;
					}
					if ( $Scale2 > 1 && $Scale2 <= $MaxDivs && !$ScaleOk )
					{
						$ScaleOk = TRUE;
						$Divisions = floor($Scale2);
						$Scale = 2;
					}
					if ( !$ScaleOk )
					{
						if ( $Scale2 > 1 )
						{
							$Factor = $Factor * 10;
						}
						if ( $Scale2 < 1 )
						{
							$Factor = $Factor / 10;
						}
					}
				}
				if ( floor($this->VMax / $Scale / $Factor) != $this->VMax / $Scale / $Factor)
				{
					$GridID	= floor ( $this->VMax / $Scale / $Factor) + 1;
					$this->VMax = $GridID * $Scale * $Factor;
					$Divisions++;
				}
				if ( floor($this->VMin / $Scale / $Factor) != $this->VMin / $Scale / $Factor)
				{
					$GridID = floor( $this->VMin / $Scale / $Factor);
					$this->VMin = $GridID * $Scale * $Factor;
					$Divisions++;
				}
			}
			else
			{
				$Scale = 1;
			}
			if ( !isset($Divisions) )
				$Divisions = 2;
			if ( $Scale == 1 && $Divisions%2 == 1 )
				$Divisions--;
		}
		else
		{
			$Divisions = $this->Divisions;
		}
		$this->DivisionCount = $Divisions;
		$DataRange = $this->VMax - $this->VMin;
		if ( $DataRange == 0 )
		{
			$DataRange = .1;
		}
		$this->DivisionHeight	= ( $this->GArea_Y2 - $this->GArea_Y1 ) / $Divisions;
		$this->DivisionRatio	= ( $this->GArea_Y2 - $this->GArea_Y1 ) / $DataRange;
		$this->GAreaXOffset		= 0;
		if ( count($Data) > 1 )
		{
			$this->DivisionWidth = ( $this->GArea_X2 - $this->GArea_X1 ) / (count($Data)-1);
		}
		else
		{
			$this->DivisionWidth = $this->GArea_X2 - $this->GArea_X1;
			$this->GAreaXOffset = $this->DivisionWidth / 2;
		}
		$this->DataCount = count($Data);
		$YPos = $this->GArea_Y2;
		$XMin = NULL;
		for ( $i=1; $i<=$Divisions+1; $i++ )
		{
			$this->drawLine($this->GArea_X1,$YPos,$this->GArea_X1-5,$YPos,$R,$G,$B);
			$Value = $this->VMin + ($i-1) * (( $this->VMax - $this->VMin ) / $Divisions);
			$Value = number_format($Value,$Decimals);
			$Position	= imageftbbox($this->FontSize,0,$this->FontName,$Value);
			$TextWidth	= $Position[2]-$Position[0];
			imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X1-10-$TextWidth,$YPos+($this->FontSize/2),$C_TextColor,$this->FontName,$Value);
			if ( $XMin > $this->GArea_X1-10-$TextWidth || $XMin == NULL )
			{
				$XMin = $this->GArea_X1-10-$TextWidth;
			}
			$YPos = $YPos - $this->DivisionHeight;
		}
		$XPos = $this->GArea_X1 + $this->GAreaXOffset;
		$ID = 1;
		$YMax = NULL;
		foreach( $Data as $Key => $Values )
		{
			if ( $ID % 1 == 0 )
			{
				$this->drawLine(floor($XPos),$this->GArea_Y2,floor($XPos),$this->GArea_Y2+5,$R,$G,$B);
				$Value = $Data[$Key][$Datacomintrotion['Position']];
				$Position	= imageftbbox($this->FontSize,0,$this->FontName,$Value);
				$TextWidth	= abs($Position[2])+abs($Position[0]);
				$TextHeight = abs($Position[1])+abs($Position[3]);
				$YPos = $this->GArea_Y2+18;
				imagettftext($this->Picture,$this->FontSize,0,floor($XPos)-floor($TextWidth/2),$YPos,$C_TextColor,$this->FontName,$Value);
				if ( $YMax < $YPos || $YMax == NULL )
				{
					$YMax = $YPos;
				}
			}
			$XPos = $XPos + $this->DivisionWidth;
			$ID++;
		}
	}
	public function drawGrid($LineWidth,$R=220,$G=220,$B=220)
	{
		// Horizontal lines
		$YPos = $this->GArea_Y2 - $this->DivisionHeight;
		for ( $i=1; $i<=$this->DivisionCount; $i++ )
		{
			if ( $YPos > $this->GArea_Y1 && $YPos < $this->GArea_Y2 )
				$this->drawDottedLine($this->GArea_X1,$YPos,$this->GArea_X2,$YPos,$LineWidth,$R,$G,$B);
			$YPos = $YPos - $this->DivisionHeight;
		}
		// Vertical lines
		if ( $this->GAreaXOffset == 0 )
		{
			$XPos = $this->GArea_X1 + $this->DivisionWidth + $this->GAreaXOffset;
			$ColCount = $this->DataCount-2;
		}
		else
		{
			$XPos = $this->GArea_X1 + $this->GAreaXOffset;
			$ColCount = $this->DataCount;
		}
		for ( $i=1; $i<=$ColCount; $i++ )
		{
			if ( $XPos > $this->GArea_X1 && $XPos < $this->GArea_X2 )
				$this->drawDottedLine(floor($XPos),$this->GArea_Y1,floor($XPos),$this->GArea_Y2,$LineWidth,$R,$G,$B);
			$XPos = $XPos + $this->DivisionWidth;
		}
	}
	public function drawTitle($XPos,$YPos,$Value,$R,$G,$B,$XPos2 = -1, $YPos2 = -1)
	{
		$C_TextColor = imagecolorallocate($this->Picture,$R,$G,$B);
		if ( $XPos2 != -1 )
		{
			$Position  = imageftbbox($this->FontSize,0,$this->FontName,$Value);
			$TextWidth = $Position[2]-$Position[0];
			$XPos      = floor(( $XPos2 - $XPos - $TextWidth ) / 2 ) + $XPos;
		}
		if ( $YPos2 != -1 )
		{
			$Position   = imageftbbox($this->FontSize,0,$this->FontName,$Value);
			$TextHeight = $Position[5]-$Position[3];
			$YPos       = floor(( $YPos2 - $YPos - $TextHeight ) / 2 ) + $YPos;
		}
		imagettftext($this->Picture,$this->FontSize,0,$XPos,$YPos,$C_TextColor,$this->FontName,$Value);
	}
	public function drawCubicCurve(&$Data,&$Datacomintrotion,$ColorID=0,$Accuracy=.1)
	{
		$GraphID = 0;
		foreach( $Datacomintrotion['Values'] as $Key2 => $ColName )
		{
			$XIn = '';
			$Yin = '';
			$Yt = '';
			$U = '';
			$XIn[0] = 0;
			$YIn[0] = 0;
			$Index = 1;
			$XLast = -1;
			$Missing = '';
			foreach( $Data as $Key => $Values )
			{
				if ( isset($Data[$Key][$ColName]) )
				{
					$Value = $Data[$Key][$ColName];
					$XIn[$Index] = $Index;
					$YIn[$Index] = $Value;
					if ( !is_numeric($Value) )
					{
						$Missing[$Index] = TRUE;
					}
					$Index++;
				}
			}
			$Index--;
			$Yt[0] = 0;
			$Yt[1] = 0;
			$U[1]  = 0;
			for ( $i=2; $i<=$Index-1; $i++ )
			{
				$Sig	= ($XIn[$i] - $XIn[$i-1]) / ($XIn[$i+1] - $XIn[$i-1]);
				$p		= $Sig * $Yt[$i-1] + 2;
				$Yt[$i] = ($Sig - 1) / $p;
				$U[$i]  = ($YIn[$i+1] - $YIn[$i]) / ($XIn[$i+1] - $XIn[$i]) - ($YIn[$i] - $YIn[$i-1]) / ($XIn[$i] - $XIn[$i-1]);
				$U[$i]  = (6 * $U[$i] / ($XIn[$i+1] - $XIn[$i-1]) - $Sig * $U[$i-1]) / $p;
			}
			$qn = 0;
			$un = 0;
			$Yt[$Index] = ($un - $qn * $U[$Index-1]) / ($qn * $Yt[$Index-1] + 1);
			for ( $k=$Index-1; $k>=1; $k-- )
				$Yt[$k] = $Yt[$k] * $Yt[$k+1] + $U[$k];// 上面两个 for 循环用来圆滑线条
			$XPos = $this->GArea_X1 + $this->GAreaXOffset;
			for ( $X=1; $X<=$Index; $X=$X+$Accuracy )
			{
				$klo = 1;
				$khi = $Index;
				$k = $khi - $klo;
				while ( $k > 1 )
				{
					$k = $khi - $klo;
					if ( $XIn[$k] >= $X )
						$khi = $k;
					else
						$klo = $k;
				}
				$klo = $khi - 1;
				$h	= $XIn[$khi] - $XIn[$klo];
				$a	= ($XIn[$khi] - $X) / $h;
				$b	= ($X - $XIn[$klo]) / $h;
				$Value = $a * $YIn[$klo] + $b * $YIn[$khi] + (($a*$a*$a - $a) * $Yt[$klo] + ($b*$b*$b - $b) * $Yt[$khi]) * ($h*$h) / 6;
				$YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);
				if ( $XLast != -1 && !isset($Missing[floor($X)]) && !isset($Missing[floor($X+1)]) )
					$this->drawLine($XLast,$YLast,$XPos,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE);
				$XLast = $XPos;
				$YLast = $YPos;
				$XPos  = $XPos + $this->DivisionWidth * $Accuracy;
			}
			$XPos = $XPos - $this->DivisionWidth * $Accuracy;
			if ( $XPos < ($this->GArea_X2 - $this->GAreaXOffset) )
			{
				$YPos = $this->GArea_Y2 - (($YIn[$Index]-$this->VMin) * $this->DivisionRatio);
				$this->drawLine($XLast,$YLast,$this->GArea_X2-$this->GAreaXOffset,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE);
			}
			$GraphID++;
		}
	}
	private function drawRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B)
	{
		$R < 0 && $R = 0;
		$R > 255 && $R = 255;
		$G < 0 && $G = 0;
		$G > 255 && $G = 255;
		$B < 0 && $B = 0;
		$B > 255 && $B = 255;
		imagecolorallocate($this->Picture,$R,$G,$B);
		$X1 = $X1-.2;
		$Y1 = $Y1-.2;
		$X2 = $X2+.2;
		$Y2 = $Y2+.2;
		$this->drawLine($X1,$Y1,$X2,$Y1,$R,$G,$B);
		$this->drawLine($X2,$Y1,$X2,$Y2,$R,$G,$B);
		$this->drawLine($X2,$Y2,$X1,$Y2,$R,$G,$B);
		$this->drawLine($X1,$Y2,$X1,$Y1,$R,$G,$B);
	}
	private function drawLine($X1,$Y1,$X2,$Y2,$R,$G,$B,$GraphFunction=FALSE)
	{
		if ( $this->LineDotSize > 1 )
		{
			$this->drawDottedLine($X1,$Y1,$X2,$Y2,$this->LineDotSize,$R,$G,$B,$GraphFunction);
			return(0);
		}
		$R < 0 && $R = 0;
		$R > 255 && $R = 255;
		$G < 0 && $G = 0;
		$G > 255 &&	$G = 255;
		$B < 0 && $B = 0;
		$B > 255 && $B = 255;
		$Distance = sqrt(($X2-$X1)*($X2-$X1)+($Y2-$Y1)*($Y2-$Y1));
		if ( $Distance == 0 )
			return(-1);
		$XStep = ($X2-$X1) / $Distance;
		$YStep = ($Y2-$Y1) / $Distance;
		for ( $i=0; $i<=$Distance; $i++ )
		{
			$X = $i * $XStep + $X1;
			$Y = $i * $YStep + $Y1;
			if ( ($X >= $this->GArea_X1 && $X <= $this->GArea_X2 && $Y >= $this->GArea_Y1 && $Y <= $this->GArea_Y2) || !$GraphFunction )
			{
				if ( $this->LineWidth == 1 )
				{
					$this->drawAntialiasPixel($X,$Y,$R,$G,$B);
				}
				else
				{
					$StartOffset = -($this->LineWidth/2);
					$EndOffset = ($this->LineWidth/2);
					for ( $j=$StartOffset; $j<=$EndOffset; $j++ )
						$this->drawAntialiasPixel($X+$j,$Y+$j,$R,$G,$B);
				}
			}
		}
	}
	private function drawDottedLine($X1,$Y1,$X2,$Y2,$DotSize,$R,$G,$B,$GraphFunction=FALSE)
	{
		$R < 0 && $R = 0;
		$R > 255 && $R = 255;
		$G < 0 && $G = 0;
		$G > 255 &&	$G = 255;
		$B < 0 && $B = 0;
		$B > 255 && $B = 255;
		$Distance = sqrt(($X2-$X1)*($X2-$X1)+($Y2-$Y1)*($Y2-$Y1));
		$XStep = ($X2-$X1) / $Distance;
		$YStep = ($Y2-$Y1) / $Distance;
		$DotIndex = 0;
		for ( $i=0; $i<=$Distance; $i++ )
		{
			$X = $i * $XStep + $X1;
			$Y = $i * $YStep + $Y1;
			if ( $DotIndex <= $DotSize )
			{
				if ( ($X >= $this->GArea_X1 && $X <= $this->GArea_X2 && $Y >= $this->GArea_Y1 && $Y <= $this->GArea_Y2) || !$GraphFunction ){
					if ( $this->LineWidth == 1 )
					{
						$this->drawAntialiasPixel($X,$Y,$R,$G,$B);
					}
					else
					{
						$StartOffset = -($this->LineWidth/2);
						$EndOffset = ($this->LineWidth/2);
						for ( $j=$StartOffset; $j<=$EndOffset; $j++ )
							$this->drawAntialiasPixel($X+$j,$Y+$j,$R,$G,$B);
					}
				}
			}
			$DotIndex++;
			if ( $DotIndex == $DotSize * 2 )
				$DotIndex = 0;
		}
	}
	private function drawAlphaPixel($X,$Y,$Alpha,$R,$G,$B)
	{
		$R < 0 && $R = 0;
		$R > 255 && $R = 255;
		$G < 0 && $G = 0;
		$G > 255 &&	$G = 255;
		$B < 0 && $B = 0;
		$B > 255 && $B = 255;
		if ( $X < 0 || $Y < 0 || $X >= $this->XSize || $Y >= $this->YSize )
			return(-1);
		$RGB2	= imagecolorat($this->Picture, $X, $Y);
		$R2		= ($RGB2 >> 16) & 0xFF;
		$G2		= ($RGB2 >> 8) & 0xFF;
		$B2		= $RGB2 & 0xFF;
		$iAlpha = (100 - $Alpha)/100;
		$Alpha	= $Alpha / 100;
		$Ra		= floor($R*$Alpha+$R2*$iAlpha);
		$Ga		= floor($G*$Alpha+$G2*$iAlpha);
		$Ba		= floor($B*$Alpha+$B2*$iAlpha);
		$C_Aliased = imagecolorallocate($this->Picture,$Ra,$Ga,$Ba);
		imagesetpixel($this->Picture,$X,$Y,$C_Aliased);
	}
	public function Render($FileName)
	{
		imagepng($this->Picture,$FileName);
	}
	private function drawAntialiasPixel($X,$Y,$R,$G,$B)
	{
		$R < 0 && $R = 0;
		$R > 255 && $R = 255;
		$G < 0 && $G = 0;
		$G > 255 &&	$G = 255;
		$B < 0 && $B = 0;
		$B > 255 && $B = 255;
		$Plot = "";
		$Xi	= floor($X);
		$Yi	= floor($Y);
		if ( $Xi == $X && $Yi == $Y){
			$C_Aliased = imagecolorallocate($this->Picture,$R,$G,$B);
			imagesetpixel($this->Picture,$X,$Y,$C_Aliased);
		}
		else
		{
			$Alpha1 = (1 - ($X - floor($X))) * (1 - ($Y - floor($Y))) * 100;
			if ( $Alpha1 > $this->AntialiasQuality )
			{
				$this->drawAlphaPixel($Xi,$Yi,$Alpha1,$R,$G,$B);
			}
			$Alpha2 = ($X - floor($X)) * (1 - ($Y - floor($Y))) * 100;
			if ( $Alpha2 > $this->AntialiasQuality )
			{
			$this->drawAlphaPixel($Xi+1,$Yi,$Alpha2,$R,$G,$B);
			}
			$Alpha3 = (1 - ($X - floor($X))) * ($Y - floor($Y)) * 100;
			if ( $Alpha3 > $this->AntialiasQuality )
			{
				$this->drawAlphaPixel($Xi,$Yi+1,$Alpha3,$R,$G,$B);
			}
			$Alpha4 = ($X - floor($X)) * ($Y - floor($Y)) * 100;
			if ( $Alpha4 > $this->AntialiasQuality )
			{
				$this->drawAlphaPixel($Xi+1,$Yi+1,$Alpha4,$R,$G,$B);
			}
		}
	}
}
?>
