function catmainxsphhover(t,order) {
	var id = $(t+'row'+order).attributes['pdtag'].value;
	var x = new Ajax();
	x.get('novel.php?mod=ajax&do=rank&novelid='+id+'&t='+t, function(s){
		ajaxinnerhtml($(t+'pdbookshow'), s);
	});
	for (var i = 1; i < 11; i++) {
		if(i==order){
			$(t+'row'+i).className = "listrow hover";
		}else{
			$(t+'row'+i).className = "listrow";
		}
	}
};