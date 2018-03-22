let currentlyDownloading = false;
$("#yt").focus();

function ajaxStream(csrfToken){
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

	const progressTotalBar = $('#progress-total');
	const progressStageBar = $('#progress-stage');

	try{
		progressTotalBar.removeClass("bg-success").removeClass("bg-danger").addClass("progress-bar-striped").text("Working");
		progressStageBar.removeClass("bg-success").removeClass("bg-danger").addClass("progress-bar-striped").text("Working");
		const stages = {0: "Downloading", 1: "Converting to MP3"};
		const numberOfStages = Object.keys(stages).length;

		const xhr = new XMLHttpRequest();
		let error = null;
		xhr.previous_text = '';
		xhr.onerror = () => {
			alert("[XHR] Fatal Error.");
		};
		xhr.onreadystatechange = () => {
			try{
				if(error !== null){
					progressTotalBar.attr('aria-valuenow', 100).removeClass("progress-bar-striped").addClass("bg-danger")
						.width("100%").text("Error");
					progressStageBar.attr('aria-valuenow', 100).removeClass("progress-bar-striped").addClass("bg-danger")
						.width("100%").text("Error");
					currentlyDownloading = false;
				}
				else
					if(xhr.readyState === 4){
						progressTotalBar.attr('aria-valuenow', 100).removeClass("progress-bar-striped")
							.addClass("bg-success").width("100%").text("Completed");
						progressStageBar.attr('aria-valuenow', 100).removeClass("progress-bar-striped")
							.addClass("bg-success").width("100%").text("Completed");
						currentlyDownloading = false;
					}
					else
						if(xhr.readyState === 3){
							let newResponse = xhr.responseText.substring(xhr.previous_text.length);
							newResponse = newResponse.substring(newResponse.lastIndexOf('{'), newResponse.lastIndexOf('}') + 1);
							const result = JSON.parse(newResponse);

							if(result.stage === -1){
								error = result.error;
								$('#Error-Modal-Text').html(error);
								$('#Error-Modal').modal({
									show: true
								});
								currentlyDownloading = false;
							}

							// Calculate Percent Done
							let totalProg = ((100 / (numberOfStages)) * result.stage) + (result.progress / numberOfStages);
							totalProg = Math.round(totalProg);
							result.progress = Math.round(result.progress);
							// Move bar forward
							progressTotalBar.attr('aria-valuenow', totalProg).width(totalProg + "%")
								.text(stages[result.stage] + " " + totalProg + "%");
							progressStageBar.attr('aria-valuenow', result.progress).width(result.progress + "%")
								.text(stages[result.stage] + " " + result.progress + "%");
							xhr.previous_text = xhr.responseText;
						}
			}
			catch(e){
			}
		};
		xhr.open("GET", "yt.php?yt=" + encodeURIComponent($("#yt").val()) + "&videoOnly="
			+ encodeURIComponent($('input[name=audio-vid]:checked').val())
			+ "&CSRF_TOKEN=" + csrfToken, true);
		xhr.send();
	}
	catch(e){
	}
}

$(function (){
	$('.input-group').each(function (){
		$(this).find('input').keypress(function (e){
			if(e.which === 10 || e.which === 13){
				$("#submitContentButton").click();
			}
		});
	});
});
