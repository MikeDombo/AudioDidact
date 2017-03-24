$(document).ready(function(){
	$(".playspeed").each(function(){
		var pb = $(this).parent().find("audio").get(0).playbackRate;
		$(this).text("Playback Speed: "+pb+"x");
	});
});
$(".playspeed").on("click", function(){
	var audioElement = $(this).parent().find("audio").get(0);
	var originalSpeed = audioElement.playbackRate;
	var newSpeed = 0;
	if(originalSpeed < 3){
		newSpeed = originalSpeed + .5;
	}
	else{
		newSpeed = 0.5;
	}
	audioElement.playbackRate = newSpeed;
	$(this).text("Playback Speed: "+newSpeed+"x");
});
$("audio").on("play", function(){
	var _this = $(this);
	$("audio").each(function(i,el){
		if(!$(el).is(_this))
			$(el).get(0).pause();
	});
});
