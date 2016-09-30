
define(['jquery', 'lang'], function ($, lang) {

    return {
        arrSystemids : [],
        strConfirm : '',
        strCurrentUrl : '',
        strCurrentTitle : '',
        strDialogTitle : '',
        strDialogStart : '',
        intTotal : 0,

        /**
         * Toggles all fields
         */
        toggleAllFields : function() {
            //batchActionSwitch
            $("table.admintable input[type='checkbox']").each(function() {
                if($(this).attr('id').substr(0, 6) == "kj_cb_" && $(this).attr('id') != 'kj_cb_batchActionSwitch') {
                    $(this)[0].checked = $('#kj_cb_batchActionSwitch')[0].checked;
                }
            });
        },

        /**
         * Toggles all fields with the given system id's
         *
         * @param arrSystemIds
         */
        toggleFields : function(arrSystemIds) {
            //batchActionSwitch
            $("table.admintable input[type='checkbox']").each(function() {
                if($(this).attr('id').substr(0, 6) == "kj_cb_" && $(this).attr('id') != 'kj_cb_batchActionSwitch') {
                    var strSysid = $(this).closest("tr").data('systemid');
                    if($.inArray(strSysid, arrSystemIds) !== -1) {//if strId in array
                        if ($(this)[0].checked) {
                            $(this)[0].checked = false;
                        } else {
                            $(this)[0].checked = true;
                        };
                    }
                }
            });
            this.updateToolbar();
        },

        updateToolbar : function() {
            if($("table.admintable  input:checked").length == 0) {
                $('.batchActionsWrapper').removeClass("visible");
            }
            else {
                $('.batchActionsWrapper').addClass("visible");
            }
        },

        triggerAction : function(strTitle, strUrl, bitRenderInfo) {
            this.arrSystemids = [];
            this.strCurrentUrl = strUrl;
            this.strCurrentTitle = strTitle;
            this.bitRenderInfo = bitRenderInfo;

            //get the selected elements
            this.arrSystemids = this.getSelectedElements();

            if(this.arrSystemids.length == 0)
                return;

            var curConfirm = this.strConfirm.replace('%amount%', this.arrSystemids.length);
            curConfirm = curConfirm.replace('%title%', strTitle);

            jsDialog_1.setTitle(this.strDialogTitle);
            jsDialog_1.setContent(curConfirm, this.strDialogStart,  'javascript:KAJONA.admin.lists.executeActions();');
            jsDialog_1.init();

            //reset pending list on hide
            var me = this;
            $('#'+jsDialog_1.containerId).on('hidden.bs.modal', function () {
                me.arrSystemids = [];
            });

            // reset messages
            if (this.bitRenderInfo) {
                $('.batchaction_messages_list').html("");
            }

            return false;
        },

        executeActions : function() {
            this.intTotal = this.arrSystemids.length;

            $('.batchActionsProgress > .progresstitle').text(this.strCurrentTitle);
            $('.batchActionsProgress > .total').text(this.intTotal);
            jsDialog_1.setContentRaw($('.batchActionsProgress').html());

            this.triggerSingleAction();
        },

        triggerSingleAction : function() {
            if(this.arrSystemids.length > 0 && this.intTotal > 0) {
                $('.batch_progressed').text((this.intTotal - this.arrSystemids.length +1));
                var intPercentage = ( (this.intTotal - this.arrSystemids.length) / this.intTotal * 100);
                $('.progress > .progress-bar').css('width', intPercentage+'%');
                $('.progress > .progress-bar').html(Math.round(intPercentage)+'%');

                var strUrl = this.strCurrentUrl.replace("%systemid%", this.arrSystemids[0]);
                this.arrSystemids.shift();

                var me = this;
                $.ajax({
                    type: 'POST',
                    url: strUrl,
                    success: function(resp) {
                        me.triggerSingleAction();
                        if (me.bitRenderInfo) {
                            var data = JSON.parse(resp);
                            if (data && data.message) {
                                $('.batchaction_messages_list').append("<li>" + data.message + "</li>");
                            }
                        }
                    },
                    dataType: 'text'
                });
            }
            else {
                $('.batch_progressed').text((this.intTotal));
                $('.progress > .progress-bar').css('width', 100+'%');
                $('.progress > .progress-bar').html('100%');

                if (!this.bitRenderInfo) {
                    document.location.reload();
                }
                else {
                    $('#jsDialog_1_cancelButton').css('display', 'none');
                    $('#jsDialog_1_confirmButton').remove('click').on('click', function() {document.location.reload();}).html('<span data-lang-property="system:systemtask_close_dialog"></span>');
                    lang.initializeProperties($('#jsDialog_1_confirmButton'));
                }
            }
        },

        /**
         * Creates an array which contains the selected system id's.
         *
         * @returns {Array}
         */
        getSelectedElements : function () {
            var selectedElements = [];

            //get the selected elements
            $("table.admintable  input:checked").each(function() {
                if($(this).attr('id').substr(0, 6) == "kj_cb_" && $(this).attr('id') != 'kj_cb_batchActionSwitch') {
                    var sysid = $(this).closest("tr").data('systemid');
                    if(sysid != "")
                        selectedElements.push(sysid);
                }
            });

            return selectedElements;
        },

        /**
         * Creates an array which contains all system id's.
         *
         * @returns {Array}
         */
        getAllElements : function () {
            var selectedElements = [];

            //get the selected elements
            $("table.admintable  input[type='checkbox']").each(function() {
                if($(this).attr('id').substr(0, 6) == "kj_cb_" && $(this).attr('id') != 'kj_cb_batchActionSwitch') {
                    var sysid = $(this).closest("tr").data('systemid');
                    if(sysid != "")
                        selectedElements.push(sysid);
                }
            });

            return selectedElements;
        }
    };

});

