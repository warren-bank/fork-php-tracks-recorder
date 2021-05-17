<?php

  //RENAME TO config.inc.php

  $_config = [];

  // [optional] if declared, require matching credentials in querystring to authorize access (?password=)
  $_config['website_pass'] = '';

  // text log file:
  // - default, enable when website doesn't require password authorization
  // - alteratively, could enable (= True;) and configure the web server to require Basic Authorization for the "/log" directory
  $_config['log_enable'] = empty($_config['website_pass']);

  // format of data received from clients; value must be an element in the set: ['owntracks','overland']
  $_config['recorder'] = 'owntracks';

  // type of database; value must be an element in the set: ['mysql','mysqlpdo','sqlite']
  // note: MariaDB is fully compatible with MySql
  $_config['sql_type'] = 'mysql';

  if ($_config['sql_type'] == 'sqlite') {
    $_config['sql_db'] = 'owntracks.db3';
  }
  else {
    $_config['sql_host']   = '';
    $_config['sql_user']   = '';
    $_config['sql_pass']   = '';
    $_config['sql_db']     = '';
    $_config['sql_prefix'] = '';
  }

  $_config['default_accuracy']  = 1000; //meters
  $_config['default_trackerID'] = 'all';

  $_config['live_map_interval'] = 10000; //milliseconds

  $_config['locale'] = [];
  // ======
  // PHP:
  //   https://www.php.net/manual/en/function.setlocale.php
  //   LC_TIME for date and time formatting with strftime()
  setlocale(LC_TIME, "en_US");
  // ======
  // datepicker:
  //   https://github.com/uxsolutions/bootstrap-datepicker/tree/v1.8.0/dist/locales
  //   [optional] if declared, value must be an element in the set: ['ar-tn','ar','az','bg','bn','br','bs','ca','cs','cy','da','de','el','en-AU','en-CA','en-GB','en-IE','en-NZ','en-ZA','eo','es','et','eu','fa','fi','fo','fr-CH','fr','gl','he','hi','hr','hu','hy','id','is','it-CH','it','ja','ka','kh','kk','km','ko','kr','lt','lv','me','mk','mn','ms','nl-BE','nl','no','oc','pl','pt-BR','pt','ro','rs-latin','rs','ru','si','sk','sl','sq','sr-latin','sr','sv','sw','ta','tg','th','tk','tr','uk','uz-cyrl','uz-latn','vi','zh-CN','zh-TW']
  $_config['locale']['datepicker'] = '';
  // ======
  // openstreetmap (geo reverse lookup)
  //   https://github.com/osm-search/Nominatim/blob/master/docs/api/Reverse.md#language-of-results
  //   either use a standard RFC2616 accept-language string or a simple comma-separated list of language codes
  $_config['locale']['openstreetmap'] = 'en';

  $_config['geo_reverse_lookup_url']      = 'https://nominatim.openstreetmap.org/reverse?format=json&zoom=18&accept-language=' . $_config['locale']['openstreetmap'] . '&addressdetails=0&email=sjobs@apple.com&';
  $_config['geo_reverse_boundingbox_url'] = 'https://nominatim.openstreetmap.org/reverse?format=json&osm_type=W&osm_id=';

?>
