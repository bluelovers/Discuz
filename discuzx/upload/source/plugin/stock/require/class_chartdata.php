<?php
/*
 * Kilofox Services
 * StockIns v9.5
 * Plug-in for Discuz!
 * Last Updated: 2011-06-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class pData
{
	private $Data;
	private $Datacomintrotion;
	public function __construct()
	{
		$this->Data							= '';
		$this->Datacomintrotion				= '';
		$this->Datacomintrotion['Position']	= 'Name';
	}
	public function AddPoint($Value,$Serie='s1')
	{
		if ( is_array($Value) && count($Value) == 1 )
			$Value = $Value[0];
		$ID = 0;
		for ( $i=0; $i<=count($this->Data); $i++ )
		{
			if ( isset($this->Data[$i][$Serie]) )
			{
				$ID = $i+1;
			}
		}
		if ( count($Value) == 1 )
		{
			$this->Data[$ID][$Serie] = $Value;
			$this->Data[$ID]['Name'] = $ID;
		}
		else
		{
			foreach( $Value as $key => $Val )
			{
				$this->Data[$ID][$Serie] = $Val;
				if ( !isset($this->Data[$ID]['Name']) )
					$this->Data[$ID]['Name'] = $ID;
				$ID++;
			}
		}
	}
	public function AddAllSeries()
	{
		unset($this->Datacomintrotion['Values']);
		if ( isset($this->Data[0]) )
		{
			foreach( $this->Data[0] as $key => $Value )
			{
				if ( $key != 'Name' )
					$this->Datacomintrotion['Values'][] = $key;
			}
		}
	}
	public function GetData()
	{
		return($this->Data);
	}
	public function GetDatacomintrotion()
	{
		return($this->Datacomintrotion);
	}
}
?>
