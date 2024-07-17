define(['jquery'], function($) {
  return {
      init: function() {
          var fields = [
              'id_s_seal_entityname',
              'id_s_seal_entitydescription',
              'id_s_seal_entitytype',
              'id_s_seal_geolocation',
              'id_s_seal_foundationyear',
              'id_s_seal_contactemail',
              'id_s_seal_contactphone',
              'id_s_seal_contactaddress',
              'id_s_seal_contactwebsite'
          ];
          fields.forEach(function(field) {
              var element = $('#' + field);
              if (element.length) {
                  element.prop('disabled', true);
              }
          });
      }
  };
});