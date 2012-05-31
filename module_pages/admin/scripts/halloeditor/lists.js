(function(jQuery) {
  return jQuery.widget("IKS.hallolists", {
    options: {
      editable: null,
      toolbar: null,
      uuid: '',
      lists: {
        ordered: false,
        unordered: true
      },
      buttonCssClass: null
    },
    _create: function() {
      var buttonize, buttonset,
        _this = this;
      buttonset = jQuery("<span class=\"" + this.widgetName + "\"></span>");
      buttonize = function(type, label) {
        var buttonElement;
        buttonElement = jQuery('<span></span>');
        buttonElement.hallobutton({
          uuid: _this.options.uuid,
          editable: _this.options.editable,
          label: label,
          command: "insert" + type + "List",
          icon: 'icon-list',
          cssClass: _this.options.buttonCssClass
        });
        return buttonset.append(buttonElement);
      };
      if (this.options.lists.ordered) {
        buttonize("Ordered", "OL");
      }
      if (this.options.lists.unordered) {
        buttonize("Unordered", "UL");
      }
      buttonset.buttonset();
      return this.options.toolbar.append(buttonset);
    },
    _init: function() {}
  });
})(jQuery);
 
