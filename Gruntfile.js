module.exports = function (grunt) {
    // Project configuration.
    var globalConfig = {
        cfg: grunt.file.readJSON('config.json')
    };

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        cfg: grunt.file.readJSON('config.json'),
        clean: {
            beforebuild: ["dist/"],
            afterbuild: ['dist/build/'],
        },
        copy: {
            main: {
                files: [

                  // bootstrap
                  { expand: true, cwd: 'bower_components/bootstrap/dist/', src: ['**'], dest: 'dist/lib/bootstrap/', filter: 'isFile' },

                  // fontawesome
                  { expand: true, cwd: 'bower_components/fontawesome/', src: ['**'], dest: 'dist/lib/fontawesome/', filter: 'isFile' },

				  // lightbox
                  { expand: true, cwd: 'nomoEFW/app/lib/lightbox/', src: ['**'], dest: 'dist/lib/lightbox/', filter: 'isFile' },

				  // select2
                  { expand: true, cwd: 'nomoEFW/app/lib/select2/', src: ['**'], dest: 'dist/lib/select2/', filter: 'isFile' },

				  // jquery-file-upload
				  { expand: true, cwd: '/nomoEFW/app/lib/jquery-file-upload/', src: ['**'], dest: 'dist/lib/jquery-file-upload/', filter: 'isFile' },

                ],
            },

        },
        html2js: {
            options: {
                // custom options, see below
                rename: function (moduleName) {
                    return '/' + moduleName.replace('../', '').replace('.html', '.html?ver=' + globalConfig.cfg.FNET_VERSION_ID);
                }
            },
            main: {
                src: ['core/**/*.html'],
                dest: 'dist/build/partials.js'
            },
        },
        cssmin: {

            libs: {
                options: {
                    target: './dist/../dist/lib/../'
                },
                files: {
                    'dist/lib/style.min.css': [
                        'dist/lib/bootstrap/css/bootstrap.css',
						'dist/lib/fontawesome/css/font-awesome.css',
						'bower_components/angular/angular-csp.css',
                        'dist/lib/lightbox/css/lightbox.css',
						'dist/lib/select2/select2.css',
						'dist/lib/select2/select2-metronic.css',
						'nomoEFW/app/lib/bootstrap-daterangepicker/daterangepicker-bs3.css',
						'nomoEFW/app/lib/bootstrap-datepicker/css/datepicker.css',
						'nomoEFW/app/lib/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
						'nomoEFW/app/lib/bootstrap-datetimepicker/css/datetimepicker.css',
						'nomoEFW/app/lib/clockface/css/clockface.css',
                        'dist/lib/jquery-file-upload/css/jquery.fileupload.css'
                    ]
                }
            },
            all: {
                options: {
                    target: './dist/../'
                },
                files: {
                    'dist/style.min.css': [
                        'dist/3rdparty_libs/style.min.css',
                        'core/common/gfx/style.css'
                    ]
                }
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> version:<%= cfg.FNET_VERSION_ID %>  */\n',
                mangle: false,
                report: 'min'
            },
            lib: {
                files: [{
                    //expand: false,
                    src: [
                        'bower_components/jquery/dist/jquery.js',
                        'bower_components/jquery-ui/jquery-ui.js',
                        'bower_components/datatables/media/js/jquery.dataTables.js',
                        'bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.js',
                        'bower_components/datatables-fixedcolumns/js/dataTables.fixedColumns.js',
                        'bower_components/bootstrap/dist/js/bootstrap.js',
                        'bower_components/bootbox/bootbox.js',
                        'bower_components/iscrolltest/src/iscroll.js',
                        'bower_components/angular/angular.js',
                        'bower_components/angular-route/angular-route.js',
                        'bower_components/angular-touch/angular-touch.js',
                        'bower_components/angular-animate/angular-animate.js',
                        'bower_components/angular-cookies/angular-cookies.js',
                        'lib/angular-translate/angular-translate.js',
                        'lib/angular-translate-loader-static-files/angular-translate-loader-static-files.js',
                        'bower_components/angular-ui-select/dist/select.js',
                        'lib/bootstrap-ui/ui-bootstrap-tpls.js',
                        'bower_components/ng-iscroll/src/ng-iscroll.js',
                        'bower_components/revolunet-angular-carousel/dist/angular-carousel.js',
                        'bower_components/angular-bootstrap-datetimepicker-github/src/js/datetimepicker.js',
                        'bower_components/moment/min/moment-with-locales.js',
                        'bower_components/angular-ui-utils/ui-utils.js',
                        'bower_components/danialfarid-angular-file-upload/dist/angular-file-upload-all.js'
                    ],
                    dest: 'dist/3rdparty_libs/3rdparty_libs.min.js'
                }]
            },
            all: {
                files: [{
                    //expand: false,
                    src: [
                        'core/effector.js',
                        'core/**/module.js',
                        'core/**/*.js'
                    ],
                    dest: 'dist/build/effector.core.min.js'
                }]
            }
        },
        concat: {
            options: {
                separator: ';'
            },
            js: {
                src: [
                    'dist/3rdparty_libs/3rdparty_libs.min.js',
                    'dist/build/partials.js',
                    'dist/build/effector.core.min.js',

                ],
                dest: 'dist/effector.min.js'
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
    grunt.registerTask('build', ['clean:beforebuild', 'copy:main', 'cssmin:libs', 'cssmin:all', 'uglify', 'concat:js', 'clean:afterbuild']); //', clean:afterbuild'
    grunt.registerTask('build-with-test', ['test-single-run', 'build']);

    // Default task.
    grunt.registerTask('default', ['build']);

};
