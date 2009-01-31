<portalupload_uploadform>
<div id="portalUploadWrapper">
	<form name="formPortalupload" method="post" action="%%formAction%%" accept-charset="UTF-8" autocomplete="off" enctype="multipart/form-data">
	    <input type="hidden" name="submitPortaluploadForm" value="1" />
	    <input type="hidden" name="portaluploadDlfolder" value="%%portaluploadDlfolder%%" />
	    %%formErrors%%%%portaluploadSuccess%%
	    <div><label for="portaluploadFile">%%portaluploadFileTitle%%</label><input type="file" name="portaluploadFile" id="portaluploadFile" class="inputText" /></div><br />
	    <div><label for="Submit"></label><input type="submit" name="Submit" value="%%submitTitle%%" class="button" /></div><br />
	</form>
</div>
</portalupload_uploadform>