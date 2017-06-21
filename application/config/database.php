<?php  if ( !defined('BASEPATH')) exit('No direct script access allowed');
              /*
              | -------------------------------------------------------------------
              | DATABASE CONNECTIVITY SETTINGS
              | -------------------------------------------------------------------
              | This file will contain the settings needed to access your database.
              |
              | For complete instructions please consult the 'Database Connection'
              | page of the User Guide.
              |
              */

              $active_group = 'default';
              $active_record = TRUE;
              $local = false;

              $db['default']['hostname'] = 'localhost';
              $db['default']['username'] = $local ? 'root' : 'appspera_usr';
              $db['default']['password'] = $local ? '' : 'w7v34yT3chSper';
              $db['default']['database'] = 'spera_crm';
              $db['default']['dbdriver'] = 'mysql';
              $db['default']['dbprefix'] = '';
              $db['default']['pconnect'] = TRUE;
              $db['default']['db_debug'] = TRUE;
              $db['default']['cache_on'] = FALSE;
              $db['default']['cachedir'] = '';
              $db['default']['char_set'] = 'utf8';
              $db['default']['dbcollat'] = 'utf8_general_ci';
              $db['default']['swap_pre'] = '';
              $db['default']['autoinit'] = TRUE;
              $db['default']['stricton'] = FALSE;


              /* End of file database.php */
              /* Location: ./application/config/database.php */
              