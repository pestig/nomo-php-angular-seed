module.exports = function (grunt) {
    // Project configuration.

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: {
            beforebuild: ['nomoEFW/app/dist/'],
            afterbuild: ['nomoEFW/app/dist/build/'],
        },
        copy: {
            main: {
                files: [

                  // bootstrap
                  { expand: true, cwd: 'bower_components/bootstrap/dist/', src: ['**'], dest: 'nomoEFW/app/dist/lib/bootstrap/', filter: 'isFile' },

                  // fontawesome
                  { expand: true, cwd: 'bower_components/fontawesome/', src: ['**'], dest: 'nomoEFW/app/dist/lib/fontawesome/', filter: 'isFile' },

				  // lightbox
                  { expand: true, cwd: 'nomoEFW/app/lib/lightbox/', src: ['**'], dest: 'nomoEFW/app/dist/lib/lightbox/', filter: 'isFile' },

				  // select2
                  { expand: true, cwd: 'nomoEFW/app/lib/select2/', src: ['**'], dest: 'nomoEFW/app/dist/lib/select2/', filter: 'isFile' },

				  // jquery-file-upload
				  { expand: true, cwd: '/nomoEFW/app/lib/jquery-file-upload/', src: ['**'], dest: 'nomoEFW/app/dist/lib/jquery-file-upload/', filter: 'isFile' },

                ],
            },

        },
        cssmin: {
            libs: {
                options: {
                    target: './dist/../nomoEFW/app/dist/lib/../'
                },
                files: {
                    'nomoEFW/app/dist/lib/style.min.css': [
                        'nomoEFW/app/dist/lib/bootstrap/css/bootstrap.css',
						'nomoEFW/app/dist/lib/fontawesome/css/font-awesome.css',
						'bower_components/angular/angular-csp.css',
                        'nomoEFW/app/dist/lib/lightbox/css/lightbox.css',
						'nomoEFW/app/dist/lib/select2/select2.css',
						'nomoEFW/app/dist/lib/select2/select2-metronic.css',
						'nomoEFW/app/lib/bootstrap-daterangepicker/daterangepicker-bs3.css',
						'nomoEFW/app/lib/bootstrap-datepicker/css/datepicker.css',
						'nomoEFW/app/lib/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
						'nomoEFW/app/lib/bootstrap-datetimepicker/css/datetimepicker.css',
						'nomoEFW/app/lib/clockface/css/clockface.css',
                        'nomoEFW/app/dist/lib/jquery-file-upload/css/jquery.fileupload.css'
                    ]
                }
            },
            all: {
                options: {
                    target: './nomoEFW/app/dist/../'
                },
                files: {
                    'nomoEFW/app/dist/nomo.min.css': [
                        'nomoEFW/app/dist/lib/style.min.css',
                        'nomoEFW/app/modules/common/css/app.css'
                    ]
                }
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> version:<%= pkg.name %>  */\n',
                mangle: false,
                report: 'min'
            },
            all: {
                files: [{
                    //expand: false,
                    src: [
                        'nomoEFW/app/modules/app.js',
                        'nomoEFW/app/modules/**/module.js',
                        'nomoEFW/app/modules/**/*.js'
                    ],
                    dest: 'nomoEFW/app/dist/build/nomo.core.min.js'
                }]
            }
        },
        concat: {
            options: {
                //separator: ';'
            },
			lib: {
                src: [
                    'bower_components/jquery/dist/jquery.min.js',
					'bower_components/jquery-ui/jquery-ui.min.js',
					'bower_components/bootstrap/dist/js/bootstrap.min.js',
					'bower_components/angular/angular.js',
					'bower_components/angular-route/angular-route.js',
					'bower_components/angular-bootstrap/ui-bootstrap.js',
					'bower_components/angular-bootstrap/ui-bootstrap-tpls.js',
					'nomoEFW/app/lib/jquery-autosize/jquery.autosize.js',
					'nomoEFW/app/lib/jquery-mask/jquery.mask.js',
					'nomoEFW/app/lib/lightbox/js/lightbox.min.js',
					'nomoEFW/app/lib/lz-string/libs/lz-string-1.3.3-min.js',
					'nomoEFW/app/lib/bootbox/bootbox.min.js',
					'nomoEFW/app/lib/bootstrap-datepicker/js/bootstrap-datepicker.js',
					'nomoEFW/app/lib/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
					'nomoEFW/app/lib/bootstrap-daterangepicker/moment.min.js',
					'nomoEFW/app/lib/bootstrap-daterangepicker/daterangepicker.js',
					//'nomoEFW/app/lib/bootstrap-colorpicker/js/bootstrap-colorpicker.js',
					'nomoEFW/app/lib/bootstrap-timepicker/js/bootstrap-timepicker.js',
					'nomoEFW/app/lib/clockface/js/clockface.js',
					'nomoEFW/app/lib/gritter/js/jquery.gritter.js',
					'nomoEFW/app/lib/jquery-file-upload/js/jquery.fileupload.js',
					'nomoEFW/app/lib/jquery-file-upload/js/jquery.iframe-transport.js',
					'nomoEFW/app/lib/select2/select2.min.js'

                ],
                dest: 'nomoEFW/app/dist/lib/all.js'
            },

			all: {
                src: [
                    'nomoEFW/app/dist/lib/all.js',
                    'nomoEFW/app/dist/build/nomo.core.min.js',

                ],
                dest: 'nomoEFW/app/dist/nomo.min.js'
            }
        },
        karma: {
            options: {
                configFile: 'test/karma.conf.js'
            },
            build: {
                singleRun: true
            },
            dev: {

            },
            phantom: {
                browsers: ['PhantomJS']
            },
            chrome: {
                browsers: ['Chrome']
            },

        },
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-karma');
    grunt.loadNpmTasks('grunt-html2js');
    grunt.loadNpmTasks('grunt-npm-install');


    // Tasks (pl.: C:> grunt test)
    grunt.registerTask('install-and-build', ['install', 'build-with-test']);
    grunt.registerTask('install', ['npm-install']);
    grunt.registerTask('test-single-run', ['karma:build']);
    grunt.registerTask('test', ['karma:dev']);
    grunt.registerTask('test-phantom', ['karma:phantom']);
    grunt.registerTask('test-chrome', ['karma:chrome']);
    grunt.registerTask('build', ['clean:beforebuild', 'copy:main', 'cssmin:libs', 'cssmin:all', 'uglify', 'concat:lib', 'concat:all', 'clean:afterbuild']);
	//grunt.registerTask('build', ['clean:beforebuild', 'uglify','concat:lib']);
    grunt.registerTask('build-with-test', ['test-single-run', 'build']);

    // Default task.
    grunt.registerTask('default', ['build']);

};
