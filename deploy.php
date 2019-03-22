<?php
namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'api.apifootball.tk');

// Project repository
set('repository', 'git@github.com:fafabedo/apifootball.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('branch', 'develop');
set('keep_releases', 3);
set('clear_paths', []);

// Shared files/dirs between deploys 
add('shared_files', [
    '.env'
]);
add('shared_dirs', [
    'public/files'
]);

// Writable dirs by web server
set('http_user', 'www-data');
add('writable_dirs', []);


// Hosts
set('default_stage', 'prod');
host('api.apifootball.tk')
    ->stage('prod')
    ->user('webdeploy')
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->set('deploy_path', '/var/www/{{application}}');
    
// Tasks

desc('Deploy ApiFootball.tk');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
//    'deploy:writable',
    'deploy:clear_paths',
    'deploy:vendors',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');

