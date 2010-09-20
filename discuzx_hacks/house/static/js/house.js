function changecategorysort(type, targetid, randomid, allsortstr, oldsortid) {
	var sortid = 0;
	if(type == 'list') {
		var obj = $(targetid);
		var sortid = parseInt(obj.options[obj.selectedIndex].value);
	} else if(type == 'tab') {
		var sortid = parseInt(targetid);
	}
	if(sortid) {
		var allsortarray = allsortstr.split('||');
		var count = allsortarray.length / 2;
		for(var i = 0; i < count; i++) {
			var m = i * 2;
			var k = i * 2 + 1;
			if(type == 'tab') {
				$('li_' + randomid + '_' + allsortarray[m]).setAttribute('class', '');
			}
			$('searchdiv_' + randomid + '_' + allsortarray[m]).style.display = 'none';
			$('dl_' + randomid + '_' + allsortarray[m]).style.display = 'none';
		}
		if(type == 'tab') {
			$('li_' + randomid + '_' + sortid).setAttribute('class', 'a');
		}
		$('searchdiv_' + randomid + '_' + sortid).style.display = '';
		$('dl_' + randomid + '_' + sortid).style.display = '';

		if(type == 'list') {
			$('changesort' + randomid + oldsortid).options.length = 0;
			for(var i = 0; i < count; i++) {
				var m = i * 2;
				var k = i * 2 + 1;
				$('changesort' + randomid + oldsortid).options[i]= new Option(allsortarray[k], allsortarray[m]);
			}
			$('changesort' + randomid + oldsortid + '_ctrl').outerHTML = '';
			simulateSelect('changesort' + randomid + oldsortid);
		}
	}
}