module.exports = function (grunt) {
    // Project configuration.
	var libFiles= grunt.file.readJSON('nomoEFW/app/lib.json');

	var jsFiles=libFiles.js;
	var cssFiles=[];
	for(var i=0;i<libFiles.css.length;i++){
		var item=libFiles.css[i];
		if(item.grunt)
			cssFiles.push(item.grunt);
		else
			cssFiles.push(item.include);
	}


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
                  { expand: true, cwd: 'bower_components/fontawesome/css', src: ['**'], dest: 'nomoEFW/app/dist/lib/fontawesome/css', filter: 'isFile' },
				  { expand: true, cwd: 'bower_components/fontawesome/fonts', src: ['**'], dest: 'nomoEFW/app/dist/lib/fontawesome/fonts', filter: 'isFile' },

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
                    target: './nomoEFW/app/dist/lib/'
                },
                files: {
                    'nomoEFW/app/dist/lib/all.min.css': cssFiles
                }
            },
            all: {
                options: {
                    target: './nomoEFW/app/dist/'
                },
                files: {
                    'nomoEFW/app/dist/nomo.min.css': [
                        'nomoEFW/app/dist/lib/all.min.css',
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
                src: jsFiles,
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
