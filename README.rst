****************
deploy-aida-docs
****************

Deployed eine ``README.rst``-Datei auf docs.aida.de

.. meta::
   :deploy-target: confluence
   :confluence-host: http://docs.aida.de
   :confluence-space: IT
   :confluence-page: aida_rsttest


==========
Verwendung
==========
::

  $ ./deploy-aida-docs.php README.rst


==============
Abhängigkeiten
==============
* rst2confluence__
* `Confluence Command line interface`__

__ https://github.com/cweiske/rst2confluence
__ https://studio.plugins.atlassian.com/wiki/display/CSOAP/Confluence+Command+Line+Interface
