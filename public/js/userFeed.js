$(document).ready(function(){
	$(".playspeed").each(function(){
		var pb = $(this).parent().find(".playback").get(0).playbackRate;
		$(this).text("Playback Speed: "+pb+"x");
	});
});
$(".playspeed").on("click", function(){
	var mediaElement = $(this).parent().find(".playback").get(0);
	var originalSpeed = mediaElement.playbackRate;
	var newSpeed = 0;
	if(originalSpeed < 3){
		newSpeed = originalSpeed + .5;
	}
	else{
		newSpeed = 0.5;
	}
	mediaElement.playbackRate = newSpeed;
	$(this).text("Playback Speed: "+newSpeed+"x");
});
$(".playback").on("play", function(){
	var $me = $(this);
	$(".playback").each(function(){
		if(!$(this).is($me)){
			$(this).get(0).pause();
		}
	});
});
