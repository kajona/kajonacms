<portalupload_uploadform>
<div id="portalUploadWrapper">
	<form name="formPortalupload" method="post" action="%%formAction%%" accept-charset="UTF-8" autocomplete="off" enctype="multipart/form-data">
	    <input type="hidden" name="submitPortaluploadForm" value="1" />
	    <input type="hidden" name="portaluploadDlfolder" value="%%portaluploadDlfolder%%" />
	    %%formErrors%%%%portaluploadSuccess%%
	    <div><label for="portaluploadFile">[lang,portaluploadFileTitle,elements]</label><input type="file" name="portaluploadFile" id="portaluploadFile" class="inputText" /></div>
	    <div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,portaluploadSubmitTitle,elements]" class="button" /></div>
	</form>
</div>
</portalupload_uploadform>