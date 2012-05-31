(function(jQuery) {
  return jQuery.widget("IKS.halloformat", {
    options: {
      editable: null,
      toolbar: null,
      uuid: "",
      formattings: {
        bold: true,
        italic: true,
        strikeThrough: false,
        underline: false
      },
      buttonCssClass: null
    },
    _create: function() {
      var buttonize, buttonset, enabled, format, widget, _ref,
        _this = this;
      widget = this;
      buttonset = jQuery("<span class=\"" + widget.widgetName + "\"></span>");
      buttonize = function(format) {
        var buttonHolder;
        buttonHolder = jQuery('<span></span>');
        buttonHolder.hallobutton({
          label: format,
          editable: _this.options.editable,
          command: format,
          uuid: _this.options.uuid,
          cssClass: _this.options.buttonCssClass
        });
        return buttonset.append(buttonHolder);
      };
      _ref = this.options.formattings;
      for (format in _ref) {
        enabled = _ref[format];
        if (enabled) {
          buttonize(format);
        }
      }
      buttonset.buttonset();
      return this.options.toolbar.append(buttonset);
    },
    _init: function() {}
  });
})(jQuery); 
