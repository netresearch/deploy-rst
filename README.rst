**********
deploy-rst
**********

Deployed eine ``README.rst``-Datei in ein Wiki, z.B. Confluence

.. meta::
   :deploy-target: confluence
   :confluence-host: http://docs.aida.de
   :confluence-space: IT
   :confluence-page: aida_rsttest


===========
Einrichtung
===========
::

  $ cp config.php.dist ~/.config/deploy-rst
  $ emacs ~/.config/deploy-rst
  .. change user and password


===========================
Meta-Angaben in rST-Dateien
===========================
Man kann in den ``.rst``-Dateien Meta-Angaben hinterlegen, damit man die Parameter
nicht alle auf der Kommandozeile angeben muss.

Beispiel::

  .. meta::
     :deploy-target: confluence
     :confluence-host: http://confluence.example.org
     :confluence-space: IT
     :confluence-page: rstpagetest

==========
Verwendung
==========
::

  $ ./deploy-rst.php README.rst

Hilfe::

  $ ./deploy-rst.php --help


==============
Abhängigkeiten
==============
* rst2confluence__
* `Confluence Command line interface`__
* ``System`` von PEAR
* ``Console_CommandLine`` von PEAR

__ https://github.com/cweiske/rst2confluence
__ https://studio.plugins.atlassian.com/wiki/display/CSOAP/Confluence+Command+Line+Interface
