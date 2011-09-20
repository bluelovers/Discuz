<?php
/*
 * Kilofox Services
 * StockIns v9.5
 * Plug-in for Discuz!
 * Last Updated: 2011-08-08
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
require_once 'class_chartdata.php';
require_once 'class_chart.php';
$DataSet = new pData;
$DataSet->AddPoint($s);
$DataSet->AddAllSeries();

$Test = new pChart(500,230);
$Test->setFixedScale(0,$rs['highprice']+1);
$Test->setFontProperties('./source/plugin/stock/font/tahoma.ttf',8);
$Test->setGraphArea(50,30,480,200);

$Test->drawGraphArea(220,220,220);//画边框
$Test->drawScale($DataSet->GetData(),$DataSet->GetDatacomintrotion(),150,150,150,2);//画坐标刻度线
$Test->drawGrid(4,230,230,230);//画网格

$Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDatacomintrotion(),$db_klcolor);//画曲线，第三个参数为曲线颜色方案

$Test->setFontProperties('./source/plugin/stock/font/tahoma.ttf',8);//如果不设置，则要查询，速度会变慢
$Test->drawTitle(50,22,"Stock Price of Recent Hours",50,50,50,500);
$Test->Render("./source/plugin/stock/data/k_{$stock_id}.png");
?>
