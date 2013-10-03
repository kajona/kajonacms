
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
                <div><label for="event_filter_status">[lang,filter_status,eventmanager]</label>
                    <select name="event_filter_status" id="event_filter_status"  class="inputText">
                        <option value="">[lang,event_status_all,eventmanager]</option>
                        <option value="0">[lang,event_status_0,eventmanager]</option>
                        <option value="1">[lang,event_status_1,eventmanager]</option>
                        <option value="2">[lang,event_status_2,eventmanager]</option>
                        <option value="3">[lang,event_status_3,eventmanager]</option>
                        <option value="4">[lang,event_status_4,eventmanager]</option>
                    </select>
                </div>
                <div><label for="event_filter_date_from">[lang,filter_date_from,eventmanager]</label>
                    <input type="text" id="event_filter_date_from" name="event_filter_date_from" value="%%event_filter_date_from%%" />
                </div>
                <div><label for="event_filter_date_to">[lang,filter_date_to,eventmanager]</label>
                    <input type="text" id="event_filter_date_to" name="event_filter_date_to" value="%%event_filter_date_to%%" />
                </div>

                <div><label for="Submit"></label><input type="submit" name="Submit" id="Submit" value="[lang,filter_button,eventmanager]" class="button" /></div>


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
        <table width="90%">
            %%events%%
        </table>
        <p><a href="%%rssurl%%">[lang,rssfeed,eventmanager]</a></p>
    </div>
</event_list>

<!-- available placeholders: title, description, location, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, detailsLinkHref, registerLinkHref, systemid, eventStatus -->
<event_list_entry>
    <tr>
        <td>%%dateFrom%%</td>
        <td width="50%" data-kajona-editable="%%systemid%%#strTitle#plain">%%title%%</td>
        <td width="20%"><a href="%%detailsLinkHref%%">[lang,detailslink,eventmanager]</a></td>
        <td width="15%"><a href="%%registerLinkHref%%">[lang,registerlink,eventmanager]</a></td>
    </tr>
</event_list_entry>

<!-- available placeholders: title, description, location, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, registerLink, registerLinkHref, maximumParticipants, systemid, eventStatus -->
<event_details>
    <div class="eventmanagerDetails">
        <h2 data-kajona-editable="%%systemid%%#strTitle#plain">%%title%%</h2>
        <p data-kajona-editable="%%systemid%%#strDescription">%%description%%</p>
        <table>
            <tr><td>[lang,location,eventmanager]</td><td data-kajona-editable="%%systemid%%#strLocation#plain">%%location%%</td></tr>
            <tr><td>[lang,dateTimeFrom,eventmanager]</td><td>%%dateTimeFrom%%</td></tr>
            <tr><td>[lang,dateTimeUntil,eventmanager]</td><td>%%dateTimeUntil%%</td></tr>
            <tr><td>[lang,maximumParticipants,eventmanager]</td><td data-kajona-editable="%%systemid%%#intParticipantsLimit#plain">%%maximumParticipants%%</td></tr>
            <tr><td>[lang,currentParticipants,eventmanager]</td><td>%%currentParticipants%%</td></tr>
            <tr><td>[lang,form_eventmanager_eventstatus,eventmanager]</td><td>[lang,event_status_%%eventStatus%%,eventmanager]</td></tr>
        </table>
        %%registerLink%%
    </div>
</event_details>

<!-- available placeholders: title, description, location, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, registerLinkHref, maximumParticipants -->
<event_details_registerlink>
    <a href="%%registerLinkHref%%">[lang,registerlink,eventmanager]</a>
</event_details_registerlink>

<!-- available placeholders: title, description, location, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, registerLinkHref, maximumParticipants -->
<event_details_updatelink>
<a href="%%registerLinkHref%%">[lang,updatelink,eventmanager]</a>
</event_details_updatelink>

<!-- available placeholders: title, location, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, formaction -->
<!-- expected form-fields: forename, lastname, email, phone, comment, form_captcha -->
<event_register>
    <div>
        <h2>%%title%%</h2>
        <table>
            <tr><td>[lang,dateTimeFrom,eventmanager]</td><td>%%dateTimeFrom%%</td></tr>
            <tr><td>[lang,dateTimeUntil,eventmanager]</td><td>%%dateTimeUntil%%</td></tr>
        </table>
        <hr />
        
        <form name="formEventRegistration" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off">
            %%formErrors%%
            <div><label for="forename">[lang,forename,eventmanager]</label><input type="text" name="forename" id="forename" value="%%forename%%" class="inputText" /></div>
            <div><label for="lastname">[lang,lastname,eventmanager]</label><input type="text" name="lastname" id="lastname" value="%%lastname%%" class="inputText" /></div>
            <div><label for="email">[lang,email,eventmanager]</label><input type="text" name="email" id="email" value="%%email%%" class="inputText" /></div>
            <div><label for="phone">[lang,phone,eventmanager]</label><input type="text" name="phone" id="phone" value="%%phone%%" class="inputText" /></div>
            <div><label for="comment">[lang,comment,eventmanager]</label><textarea name="comment" id="comment" class="inputTextarea">%%comment%%</textarea></div>
            <div><label for="kajonaCaptcha_eventreg"></label><span id="kajonaCaptcha_eventreg"><script type="text/javascript">KAJONA.portal.loadCaptcha('eventreg', 180);</script></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('eventreg', 180); return false;">[lang,commons_captcha_reload,eventmanager]</a>)</div>
            <div><label for="form_captcha">[lang,commons_captcha,eventmanager]</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" autocomplete="off" /></div>
            <div><label for="Submit"></label><input type="submit" name="Submit" id="Submit" value="[lang,registerSubmit,eventmanager]" class="button" /></div>
        </form>

    </div>
</event_register>


<!-- the current user is logged in, so known to the system, a slightly different form may be shown instead -->
<!-- available placeholders: title, location, dateTimeFrom, dateFrom, dateTimeUntil, dateUntil, formaction, username -->
<!-- expected form-fields: comment, participant_status (please note, that 1 (accepted) and 2 (declined) are reserved -->
<event_register_loggedin>
<div>
    <h2>%%title%%</h2>
    <table>
        <tr><td>[lang,dateTimeFrom,eventmanager]</td><td>%%dateTimeFrom%%</td></tr>
        <tr><td>[lang,dateTimeUntil,eventmanager]</td><td>%%dateTimeUntil%%</td></tr>
    </table>
    <hr />

    <form name="formEventRegistration" method="post" action="%%formaction%%" accept-charset="UTF-8" autocomplete="off">
        %%formErrors%%
        <div><label for="">[lang,username,eventmanager]</label>%%username%%</div>
        <div><label for="comment">[lang,comment,eventmanager]</label><textarea name="comment" id="comment" class="inputTextarea">%%comment%%</textarea></div>
        <div>
            <label for="participant_status">[lang,participant_status,eventmanager]</label>
            <select name="participant_status" id="participant_status">
                <option value="1">[lang,participant_status_1,eventmanager]</option>
                <option value="2">[lang,participant_status_2,eventmanager]</option>
                <option value="3">[lang,participant_status_3,eventmanager]</option>
            </select>
            <script type="text/javascript">$('#participant_status').val('%%participant_status%%')</script>
        </div>
        <div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,registerSubmit,eventmanager]" class="button" /></div>
    </form>

</div>
</event_register_loggedin>

<event_register_message>
    <div>
        <h2>%%title%%</h2>
        <p>%%message%%</p>
    </div>
</event_register_message>

<error_row>
    &bull; %%error%%<br />
</error_row>