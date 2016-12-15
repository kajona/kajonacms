
module.exports = function(grunt) {

  grunt.initConfig({
    uglify: {
      options: {
      },
      build: {
        src: [
          '../../core*/module_*/scripts/*.js'
        ],
        dest: 'kajona.min.js'
      }
    },
    concat: {
	  options: {
	    separator: ';' + "\n"
	  },
	  dist: {
	    src: [
          '../../core/module_system/scripts/jquery/jquery.min.js',
          '../../core/module_system/scripts/jquerytag/jquery.tag-editor.min.js',
          '../../core/module_system/scripts/jqueryui/jquery-ui.custom.min.js',
          '../../core/module_system/scripts/jstree/jquery.jstree.js',
          '../../core/module_system/scripts/qtip2/jquery.qtip.min.js',
          '../../kajona.min.js'
        ],
	    dest: './kajona.all.js'
	  }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-concat');

  grunt.registerTask('default', ['uglify', 'concat']);

};
