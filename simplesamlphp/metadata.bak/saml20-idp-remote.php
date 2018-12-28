<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote 
 */

 /*
$metadata['https://example.com'] = array(
    'SingleSignOnService'  => 'https://example.com/simplesaml/saml2/idp/SSOService.php',
    'SingleLogoutService'  => 'https://example.com/simplesaml/saml2/idp/SingleLogoutService.php',
    'certificate'          => 'saml.pem',
);
*/


$metadata['http://fs.pptplus.cn/adfs/services/trust'] = array (
  'entityid' => 'http://fs.pptplus.cn/adfs/services/trust',
  'contacts' => 
  array (
  ),
  'metadata-set' => 'saml20-idp-remote',
  'SingleSignOnService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://fs.pptplus.cn/adfs/ls/',
    ),
    1 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'https://fs.pptplus.cn/adfs/ls/',
    ),
  ),
  'SingleLogoutService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://fs.pptplus.cn/adfs/ls/',
    ),
    1 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'https://fs.pptplus.cn/adfs/ls/',
    ),
  ),
  'ArtifactResolutionService' => 
  array (
  ),
  'NameIDFormats' => 
  array (
    0 => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
    1 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    2 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
  ),
  'keys' => 
  array (
    0 => 
    array (
      'encryption' => true,
      'signing' => false,
      'type' => 'X509Certificate',
      'X509Certificate' => 'MIIC3DCCAcSgAwIBAgIQcwPPAoQ6iJxNJVXEMPCckzANBgkqhkiG9w0BAQsFADAqMSgwJgYDVQQDEx9BREZTIEVuY3J5cHRpb24gLSBmcy5wcHRwbHVzLmNuMB4XDTE4MDkxMjEwMDQwNFoXDTE5MDkxMjEwMDQwNFowKjEoMCYGA1UEAxMfQURGUyBFbmNyeXB0aW9uIC0gZnMucHB0cGx1cy5jbjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAJ5gHjXwFjbsaqPcI+4CJ76zWL7AFgeEsbw6QKjnwJxrDQcE6MgRF9s5H8XRlYUY8hqmX3oS/Kc93d+ODoQEnOOc5s2ustm0p2Q7tflZoZfai8B+fPVT49TgzvmKAgUXtUs2ukISiWawpljtNUusb7MxlcfMCnqi03MJWWU2achDrawOElkPu4+oi6mB0fTmVZABN69v2UAf1C2jrd6P/rhHp9p9SyrQZ/k9VCsoph/OKEevMT49rA3VEFDoTKJ4O2TqTWHYpWPIvuBhloX6zmHxieLOPCquMDHVDV9PbAIt43sLKxRtgwmCP9fm1yu0dV59XiNebhBSK530NZUgBvUCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAE32csQDkzKV+vAfEDaigA3f0gChfwCio55k8OPf9wiDGIuC2gWzY7woKGz14StSLhW1o+Nx9d0iFRp3vGTaTjqTDgN1hOoDRsyzHy+W1qtNFhEk3GgeOjpjFwW+Cxf4wzdOsb0mN7Ai1yRqE4cy8wDZJp7KHJw/kSaNx6pxBjrmiNqzb52DEysiaj32fdfS8ZfsXyxaH7arEq09DuiPfpuC6awdAnXbWuN9oT74yCXGi6dS2ay7+ydhMxYGKeOi+33r1IZVyemPFs+gzW5Pe1tv9vs7otuf4t7Xml/41xi78SwP8jNDVwV1neHeCnQQbvbYPg3N3Dd6v9X3cyAIS8Q==',
    ),
    1 => 
    array (
      'encryption' => false,
      'signing' => true,
      'type' => 'X509Certificate',
      'X509Certificate' => 'MIIC1jCCAb6gAwIBAgIQMa2spKo82LNIR9sIawnPDzANBgkqhkiG9w0BAQsFADAnMSUwIwYDVQQDExxBREZTIFNpZ25pbmcgLSBmcy5wcHRwbHVzLmNuMB4XDTE4MDkxMjEwMDQwNFoXDTE5MDkxMjEwMDQwNFowJzElMCMGA1UEAxMcQURGUyBTaWduaW5nIC0gZnMucHB0cGx1cy5jbjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANBmpa75V5suNwb5pFSVPNLwQS8hFDTIbAUPB7F+YxO7ApsdBLQj7Ez+TNNaLL2bhYMQba0V8K6pR5+IOl2slXu7iFFlr/No/NmgjXSImmqa1xWdOdYfdZ4Bmk0C99fJvAWO3TuA9ziMwHTQR6YZ4JdnBIGyIkdBzyLPgyONFUVEb4cYuLfVOq2stHtjiPViZlvOsuH16khIPxvdSyIj5hM8QiJxAmTRLz9xdaO2OATQV+vgGcsV4Krw8VdkCzfhYXYRpFzRLtYbO13YiJd+eJF77tR8Gw7Yjwojx8qcc8/jnuX/HegyeLKvRnJKlIwlvyXPFrvpVDoTCTvK7Usza/sCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAM5ilTda67mLD9nZy/zu76YlAnga/2AP5STiuEiFBmEOApF9sY/VSYnEBgjLEi6rE+21zsWv545UvjQOu2Lx8CsgOLy++Krk90swOWQNeU5m9yy2dnlWjFjnTQPHXy2/PNSVM7sJtgkglrLAzybtBVQQPp3owU+UoXuXHr8OtlbVWXPvH0q4KmBXYdFo3M2Pj/LAJg5+muZ0Nwxk9Ki1KN7fw1gh+TJYsl7D7KcFT+lgNrKUcmrbmwNb0ANYB6VXYg9xpePfCTVwJwr0fdKnoKorSdoa0waiUhsdK6k5g6wOCGEBXlH3eYMYt0kSOhMCZTfzvO5b0BGyRvaPDGf5g8A==',
    ),
  ),
);

