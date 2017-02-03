
<!-- available placeholders: cal_eventsource, rssurl -->
<event_calendar>
    <script type='text/javascript'>
        KAJONA.portal.loader.loadFile([
                KAJONA_WEBPATH+"/templates/default/js/fullcalendar/fullcalendar.min.js",
                KAJONA_WEBPATH+"/templates/default/js/fullcalendar/fullcalendar.css"
            ], 
            function() {
                 //files are loaded, initialize the calendar...
                 $('#eventmanagerCalendar').fullCalendar({
                     dayNames: [ [lang,toolsetCalendarWeekday,system] ],
                     dayNamesShort: [ [lang,toolsetCalendarWeekday,system] ],
                     monthNames: [ [lang,toolsetCalendarMonth,system] ],
                     monthNamesShort: [ [lang,toolsetCalendarMonth,system] ],
                     buttonText: {
                        today: '[lang,cal_today,eventmanager]'
                     },
                     events: "%%cal_eventsource%%",
                     firstDay: 1
            })
        }, true);
    </script>
    <div id="eventmanagerCalendar"></div>
    <p><a href="%%rssurl%%">[lang,rssfeed,eventmanager]</a></p>
</event_calendar>


<!-- available placeholders: events, rssurl, formaction, event_filter_status, event_filter_date_from, event_filter_date_to -->
<event_list>
    <div class="eventmanagerList">
        <div class="eventmanagerFilter">
            <form name="eventmanagerFilter" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off">
                <fieldset class="form-group">
                    <label for="event_filter_status">[lang,filter_status,eventmanager]</label>
                    <select name="event_filter_status" id="event_filter_status"  class="form-control">
                        <option value="">[lang,event_status_all,eventmanager]</option>
                        <option value="0">[lang,event_status_0,eventmanager]</option>
                        <option value="1">[lang,event_status_1,eventmanager]</option>
                        <option value="2">[lang,event_status_2,eventmanager]</option>
                        <option value="3">[lang,event_status_3,eventmanager]</option>
                        <option value="4">[lang,event_status_4,eventmanager]</option>
                    </select>
                </fieldset>
                <fieldset class="form-group">
                    <label for="event_filter_date_from">[lang,filter_date_from,eventmanager]</label>
                    <div class="row">
                        <div class="col-xs-3">
                            <input type="text" id="event_filter_date_from" name="event_filter_date_from" value="%%event_filter_date_from%%" class="form-control-sm form-control" />
                        </div>
                    </div>
                </fieldset>
                <fieldset class="form-group">
                    <label for="event_filter_date_to">[lang,filter_date_to,eventmanager]</label>
                    <div class="row">
                        <div class="col-xs-3">
                            <input type="text" id="event_filter_date_to" name="event_filter_date_to" value="%%event_filter_date_to%%" class="form-control-sm form-control" />
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-group">
                    <button type="submit" class="btn btn-primary">[lang,filter_button,eventmanager]</button>
                </fieldset>

                <script type="text/javascript">
                    $('#event_filter_status').val('%%event_filter_status%%');
                    KAJONA.portal.loader.loadFile([
                        '/templates/default/js/zebradatepicker/css/bootstrap.css',
                        '/templates/default/js/zebradatepicker/javascript/zebra_datepicker.js'
                    ], function() {
                        $('#event_filter_date_from').Zebra_DatePicker({
                            format: 'Y-m-d'
                        });
                        $('#event_filter_date_to').Zebra_DatePicker({
                            format: 'Y-m-d'
                        });
                    } );
                </script>

            </form>
        </div>
        <table class="table table-striped">
            %%events%%
        </table>
        <p><a href="%%rssurl%%" class="btn btn-outline-info"><i class="fa fa-rss"></i> [lang,rssfeed,eventmanager]</a></p>
    </div>
</event_list>

<!-- available placeholders: strTitle, strDescription, strLocation, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, detailsLinkHref, registerLinkHref, strSystemid, intEventStatus -->
<event_list_entry>
    <tr>
        <td>%%dateFrom%%</td>
        <td width="50%" data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</td>
        <td width="20%"><a href="%%detailsLinkHref%%">[lang,detailslink,eventmanager]</a></td>
        <td width="15%"><a href="%%registerLinkHref%%">[lang,registerlink,eventmanager]</a></td>
    </tr>
</event_list_entry>

<!-- available placeholders: strTitle, strDescription, intLocation, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, registerLink, registerLinkHref, intMaximumParticipants, strSystemid, intEventStatus -->
<event_details>
    <div class="eventmanagerDetails">
        <h2 data-kajona-editable="%%strSystemid%%#strTitle#plain">%%strTitle%%</h2>
        <p data-kajona-editable="%%strSystemid%%#strDescription">%%strDescription%%</p>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,location,eventmanager]</div><div class="col-md-8" data-kajona-editable="%%strSystemid%%#strLocation#plain">%%strLocation%%</div>
        </div>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,dateTimeFrom,eventmanager]</div><div class="col-md-8">%%dateTimeFrom%%</div>
        </div>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,dateTimeUntil,eventmanager]</div><div class="col-md-8">%%dateTimeUntil%%</div>
        </div>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,maximumParticipants,eventmanager]</div><div class="col-md-8" data-kajona-editable="%%strSystemid%%#intParticipantsLimit#plain">%%intParticipantsLimit%%</div>
        </div>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,currentParticipants,eventmanager]</div><div class="col-md-8">%%currentParticipants%%</div>
        </div>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,form_eventmanager_eventstatus,eventmanager]</div><div class="col-md-8">[lang,event_status_%%intEventStatus%%,eventmanager]</div>
        </div>
        %%registerLink%%
    </div>
</event_details>

<!-- available placeholders: strTitle, strDescription, intLocation, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, registerLinkHref, intMaximumParticipants -->
<event_details_registerlink>
    <a href="%%registerLinkHref%%" class="btn btn-outline-info">[lang,registerlink,eventmanager]</a>
</event_details_registerlink>

<!-- available placeholders: strTitle, strDescription, strLocation, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, registerLinkHref, intMaximumParticipants -->
<event_details_updatelink>
<a href="%%registerLinkHref%%" class="btn btn-outline-info">[lang,updatelink,eventmanager]</a>
</event_details_updatelink>

<!-- available placeholders: strTitle, strLocation, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, formaction -->
<!-- expected form-fields: forename, lastname, email, phone, comment, form_captcha -->
<event_register>
    <div>
        <h2>%%title%%</h2>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,dateTimeFrom,eventmanager]</div><div class="col-md-8">%%dateTimeFrom%%</div>
        </div>
        <div class="row">
            <div class="col-md-4 text-xs-right">[lang,dateTimeUntil,eventmanager]</div><div class="col-md-8">%%dateTimeUntil%%</div>
        </div>

        <hr />
        
        %%formErrors%%
        <form name="formEventRegistration" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off">
            <fieldset class="form-group">
                <label for="forename">[lang,forename,eventmanager]</label><input type="text" name="forename" id="forename" value="%%forename%%" class="form-control" />
            </fieldset>
            <fieldset class="form-group">
                <label for="lastname">[lang,lastname,eventmanager]</label><input type="text" name="lastname" id="lastname" value="%%lastname%%" class="form-control" />
            </fieldset>
            <fieldset class="form-group">
                <label for="email">[lang,email,eventmanager]</label><input type="text" name="email" id="email" value="%%email%%" class="form-control" />
            </fieldset>
            <fieldset class="form-group">
                <label for="phone">[lang,phone,eventmanager]</label><input type="text" name="phone" id="phone" value="%%phone%%" class="form-control" />
            </fieldset>
            <fieldset class="form-group">
                <label for="comment">[lang,comment,eventmanager]</label><textarea name="comment" id="comment" class="form-control">%%comment%%</textarea>
            </fieldset>

            <fieldset class="form-group">
                <label for="form_captcha">[lang,commons_captcha,elements]</label>

                <div class="row">
                    <div class="col-xs-3">
                        <input type="text" name="form_captcha" id="form_captcha" class="form-control" autocomplete="off" />
                        <small class="text-muted"><a href="#" onclick="KAJONA.portal.loadCaptcha('eventreg', 180); return false;">[lang,commons_captcha_reload,elements]</a></small>
                    </div>
                    <div class="col-xs-6">
                        <span id="kajonaCaptcha_eventreg"><script type="text/javascript">KAJONA.portal.loadCaptcha('eventreg', 180);</script></span>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-group">
                <button type="submit" class="btn btn-primary">[lang,registerSubmit,eventmanager]</button>
            </fieldset>
        </form>
        <!-- custom bootstrap error rendering, update if required -->
        <script type="text/javascript">
            $.each([%%error_fields%%], function(index, value) {
                $('#'+value).addClass('form-control-danger');
                $('#'+value).closest('.form-group').addClass('has-danger');
            });
        </script>
    </div>
</event_register>


<!-- the current user is logged in, so known to the system, a slightly different form may be shown instead -->
<!-- available placeholders: strTitle, strLocation, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, formaction, username -->
<!-- expected form-fields: comment, participant_status (please note, that 1 (accepted) and 2 (declined) are reserved -->
<event_register_loggedin>
<div>
    <h2>%%title%%</h2>
    <div class="row">
        <div class="col-md-2 text-xs-right">[lang,dateTimeFrom,eventmanager]</div><div class="col-md-10">%%dateTimeFrom%%</div>
    </div>
    <div class="row">
        <div class="col-md-2 text-xs-right">[lang,dateTimeUntil,eventmanager]</div><div class="col-md-10">%%dateTimeUntil%%</div>
    </div>
    <hr />

    %%formErrors%%
    <form name="formEventRegistration" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off">
        <fieldset class="form-group">
            <label>[lang,username,eventmanager]</label><span class="form-control">%%username%%</span>
        </fieldset>
        <fieldset class="form-group">
            <label for="comment">[lang,comment,eventmanager]</label><textarea name="comment" id="comment" class="form-control">%%comment%%</textarea>
        </fieldset>
        <fieldset class="form-group">
            <label for="participant_status">[lang,participant_status,eventmanager]</label>
            <select name="participant_status" id="participant_status" class="form-control">
                <option value="1">[lang,participant_status_1,eventmanager]</option>
                <option value="2">[lang,participant_status_2,eventmanager]</option>
                <option value="3">[lang,participant_status_3,eventmanager]</option>
            </select>
            <script type="text/javascript">$('#participant_status').val('%%participant_status%%')</script>
        </fieldset>
        <fieldset class="form-group">
            <button type="submit" class="btn btn-primary">[lang,registerSubmit,eventmanager]</button>
        </fieldset>
    </form>

    <!-- custom bootstrap error rendering, update if required -->
    <script type="text/javascript">
        $.each([%%error_fields%%], function(index, value) {
            $('#'+value).addClass('form-control-danger');
            $('#'+value).closest('.form-group').addClass('has-danger');
        });
    </script>

</div>
</event_register_loggedin>

<event_register_message>
    <div>
        <h2>%%title%%</h2>
        <div class="alert alert-success" role="alert">%%message%%</div>
    </div>
</event_register_message>

<error_row>
    &bull; %%error%%<br />
</error_row>


<!-- available placeholders: error_list -->
<errors>
    <div class="alert alert-danger" role="alert">
        <ul>%%error_list%%</ul>
    </div>
</errors>