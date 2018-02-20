var currentlyDownloading = false;
$("#yt").focus();

function ajaxStream(){
	if(!$("#yt").val()){
		return;
	}
	if(!window.XMLHttpRequest){
		alert("Your browser does not support the native XMLHttpRequest object.");
		return;
	}
	if(currentlyDownloading){
		alert("Wait for the current download to complete");
		return;
	}
	else{
		currentlyDownloading = true;
	}

	try{
		$('#progress-total').removeClass("bg-success").removeClass("bg-danger").addClass("progress-bar-striped").text("Working");
		$('#progress-stage').removeClass("bg-success").removeClass("bg-danger").addClass("progress-bar-striped").text("Working");
		var stages = {0: "Downloading", 1: "Converting to MP3"};
		var numberOfStages = Object.keys(stages).length;

		var xhr = new XMLHttpRequest();
		var error = null;
		xhr.previous_text = '';
		xhr.onerror = function (){
			alert("[XHR] Fatal Error.");
		};
		xhr.onreadystatechange = function (){
			try{
				if(error !== null){
					$('#progress-total').attr('aria-valuenow', 100).removeClass("progress-bar-striped").addClass("bg-danger")
						.width("100%").text("Error");
					$('#progress-stage').attr('aria-valuenow', 100).removeClass("progress-bar-striped").addClass("bg-danger")
						.width("100%").text("Error");
					currentlyDownloading = false;
				}
				else
					if(xhr.readyState === 4){
						$('#progress-total').attr('aria-valuenow', 100).removeClass("progress-bar-striped")
							.addClass("bg-success").width("100%").text("Completed");
						$('#progress-stage').attr('aria-valuenow', 100).removeClass("progress-bar-striped")
							.addClass("bg-success").width("100%").text("Completed");
						currentlyDownloading = false;
					}
					else
						if(xhr.readyState === 3){
							var newResponse = xhr.responseText.substring(xhr.previous_text.length);
							newResponse = newResponse.substring(newResponse.lastIndexOf('{'), newResponse.lastIndexOf('}') + 1);
							var result = JSON.parse(newResponse);

							if(result.stage === -1){
								error = result.error;
								$('#Error-Modal-Text').html(error);
								$('#Error-Modal').modal({
									show: true
								});
								currentlyDownloading = false;
							}

							// Calculate Percent Done
							var totalProg = ((100 / (numberOfStages)) * result.stage) + (result.progress / numberOfStages);
							totalProg = Math.round(totalProg);
							result.progress = Math.round(result.progress);
							// Move bar forward
							$('#progress-total').attr('aria-valuenow', totalProg).width(totalProg + "%")
								.text(stages[result.stage] + " " + totalProg + "%");
							$('#progress-stage').attr('aria-valuenow', result.progress).width(result.progress + "%")
								.text(stages[result.stage] + " " + result.progress + "%");
							xhr.previous_text = xhr.responseText;
						}
			}
			catch(e){
			}
		};
		xhr.open("GET", "yt.php?yt=" + encodeURIComponent($("#yt").val()) + "&videoOnly=" + encodeURIComponent($('input[name=audio-vid]:checked').val()) + "&CSRF_TOKEN=" + csrfToken, true);
		xhr.send();
	}
	catch(e){
	}
}

$(function (){
	$('.input-group').each(function (){
		$(this).find('input').keypress(function (e){
			if(e.which === 10 || e.which === 13){
				ajaxStream();
			}
		});
	});
});
