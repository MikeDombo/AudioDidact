var jsmediatags = window.jsmediatags;
document.getElementById("yt").addEventListener("change", function (event){
	var file = event.target.files[0];
	jsmediatags.read(file, {
		onSuccess: function (tag){
			if($("#title").val() == ""){
				$("#title").val(tag.tags.title);
			}
			if($("#author").val() == ""){
				$("#author").val(tag.tags.artist);
			}
			if($("#description").val() == ""){
				$("#description").val(tag.tags.comment.text);
			}
			var image = tag.tags.picture;
			if(image){
				var base64String = "";
				for(var i = 0; i < image.data.length; i++){
					base64String += String.fromCharCode(image.data[i]);
				}
				var base64 = "data:" + image.format + ";base64," +
					window.btoa(base64String);
				document.getElementById('picture').setAttribute('src', base64);
				$("#art").val(base64);
				document.getElementById('picture').style.display = "";
			}
		},
		onError: function (error){
			console.log(error);
		}
	});
}, false);

$(document).ready(function (){
	var options = {
		beforeSend: function (){
			$("#progress").width('0%').text("");
			window.onbeforeunload = function(e) {
				return "Please make sure the upload has finished before closing this window.";
			};
		},
		uploadProgress: function (event, position, total, percentComplete){
			$("#progress").width(percentComplete + '%').text(percentComplete + '%');
		},
		success: function (){
			$("#progress").width('100%').text('100%');
		},
		complete: function (response){
			console.log(response);
			try{
				var r = JSON.parse(response.responseText);
				if(r["error"] == false){
					$("#progress").text("Successfully Uploaded!");
				}
				else{
					$('#Error-Modal-Text').html(r['error']);
					$('#Error-Modal').modal({
						show: true
					});
					$("#progress").text(r["error"]);
				}
			}
			catch(e){
				console.log(e);
			}
			window.onbeforeunload = null;
		},
		error: function (response){
			console.log(response);
			$("#progress").text("ERROR: unable to upload files");
			window.onbeforeunload = null;
		}
	};
	$("#myForm").ajaxForm(options);
});

function getBase64(file){
	var reader = new FileReader();
	reader.readAsDataURL(file);
	reader.onload = function (){
		$("#art").val(reader.result);
		document.getElementById('picture').setAttribute('src', reader.result);
		document.getElementById('picture').style.display = "";
	};
	reader.onerror = function (error){
		console.log('Error: ', error);
	};
}

document.getElementById("albumArtFile").addEventListener("change", function (event){
	var file = event.target.files[0];
	if(file.type.indexOf("image") > -1){
		getBase64(file);
	}
	else{
		$('#Error-Modal-Text').html("Art must be an image!");
		$('#Error-Modal').modal({
			show: true
		});
	}
}, false);

document.getElementById("albumArtURL").addEventListener("change", function (){
	if($("#albumArtURL").val() != ""){
		$("#art").val($("#albumArtURL").val());
		document.getElementById('picture').setAttribute('src', $("#albumArtURL").val());
		document.getElementById('picture').style.display = "";
	}
}, false);
