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
      'X509Certificate' => 'MIIC3DCCAcSgAwIBAgIQRlWrQrv97oBKXccqHoZUbDANBgkqhkiG9w0BAQsFADAqMSgwJgYDVQQDEx9BREZTIEVuY3J5cHRpb24gLSBmcy5wcHRwbHVzLmNuMB4XDTE4MTAxODAzMzUzMFoXDTE5MTAxODAzMzUzMFowKjEoMCYGA1UEAxMfQURGUyBFbmNyeXB0aW9uIC0gZnMucHB0cGx1cy5jbjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAK+7+Dxnj1nIw2kuo+cDQIAviOs2QWYJTm7Q4e5XfVJQjHqt5Zk+Hk0RnsO43dfQeoCvakIP6OPfynRenIiAarR2nAsUiJiJA1jOz7DfOXCnOLI3uo5eQrirtAfakncrQFd+JDD/ArXY80IuTy0Cm8ga3YgJyAYm5CrPVShSpztpScRUZhaBwLGG4yJ6s19QN3558vlQM19qJHsHMMr+GByYrSPoiQZMJiChf5FqigZHFDi1qNFaKMU8wuSoOWpQSUUzAVfSuuN4t0DF1wkfEEN20hz+6bz0CPm+GLr+5348G05H6ypXhju2PNqw9wxRMIoYPDLV+l3yeDZUCzsK0jUCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAeh0Cgmif3OpLAVv1nCj71drk/dsoVh9MHuJ5S3HOF+H6mPiZnaqo6evz5G9DYEQoenx303ebP1zNfrbgZlaQHCSGg/fpqZu4utUbiS9Iy8Ada0rvCGyUU9dDkPP84oW5vLJtC+ouxKl+WbXj+l/kO1tV/MkdmQp8cVyS9bxKjFrm1OFzN5WvoCp255NdbYSHhoCJ5a5fbzTb1M5pyOSCox/brgIiHAkwdoeDbpDh4iNDobLiC84vW198t9mmnqVxagUSYP+pI5c76Eve3KFN9HGnaBC3kgZX2NJBaznQs62OKqUhsjBDPJbwUmQmFWO3E6RP5c+2T5OlzQKF6AzW+g==',
    ),
    1 => 
    array (
      'encryption' => false,
      'signing' => true,
      'type' => 'X509Certificate',
      'X509Certificate' => 'MIIC1jCCAb6gAwIBAgIQOYPl4xuPzoZBMsUIA+mroTANBgkqhkiG9w0BAQsFADAnMSUwIwYDVQQDExxBREZTIFNpZ25pbmcgLSBmcy5wcHRwbHVzLmNuMB4XDTE4MTAxODAzMzUzMFoXDTE5MTAxODAzMzUzMFowJzElMCMGA1UEAxMcQURGUyBTaWduaW5nIC0gZnMucHB0cGx1cy5jbjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALtRFRhpE68k+YyspoEzxwGqEfwfb+5K5PZqA1SfbdDispjHJFTAm26edUqaSklLwRcaY3iY0HlMhhZCOt5ZPOHcgCJCSZBIyHibtsHGigscn9JaZfX6FLRIV68baFZ26RAF47xI7vM3zkWOtr0T5MA2KJC32uB6pAmNV7jJ83MJiz/BOH6HUQ2EP6P4NOCXsDFSLwO3nkDA3BkDHUAMH3N0LdJ58ETH7uMt8ffuiNOFyaLsPpjmq2UzPB74t8JbLpa+41R0KdO2IkUZ6XS1XFJnXN4DwsYKEgUXu6DHegVdP/uECVYgJn6Lw8pV4uFO4U4sFJRGCE0fYKUopD5rJm0CAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAZraQ35h+cW0jwrOfcntYElhVrjwrQGiUsZl63oPU1Uo4x4FGPYiC0cz7pCrX5OWsOHs0fk5Xc3zzmbVAFmSqGV0otqt90V1hh/uCYR2SPG22OYDASkfOAjg+Oqd0VJT4mxLkDWGv6lAx61/IkqEzZW+57cgOFmAasEHzqkSb9CBUEibbH06aVXgYoMcgobv0qLHP3g7hKYHijEOSPLrpFVIetmUrdDDzFvPFJDxP9cKi9B0e06TT3haUkm/yVt+RoiYTuQEhkx3rzKYbBXEmYCGGQxvbkIrnXiYJVRxjBS+g49rLoc0PhJK8rasFlJoUmuCuPphZSlBy6AvKqJx8cw==',
    ),
  ),
);

