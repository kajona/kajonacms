
module.exports = function(grunt) {

  grunt.initConfig({
    uglify: {
      options: {
      },
      build: {
        src: [
          '../../core*/module_*/admin/scripts/*.js'
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
          '../../core/module_system/admin/scripts/jquery/jquery.min.js',
          '../../core/module_system/admin/scripts/jquerytag/jquery.tag-editor.min.js',
          '../../core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js',
          '../../core/module_system/admin/scripts/jstree/jquery.jstree.js',
          '../../core/module_system/admin/scripts/qtip2/jquery.qtip.min.js',
          '../../kajona.min.js'
        ],
	    dest: './kajona.all.js'
	  }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-concat-css');

  grunt.registerTask('default', ['uglify', 'concat']);

};
