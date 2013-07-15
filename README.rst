**********
deploy-rst
**********

Deploys a ``README.rst`` file into a wiki, e.g. Confluence

.. meta::
   :deploy-target: confluence
   :confluence-host: http://confluence.example.org
   :confluence-space: IT
   :confluence-page: rstpagetest


============
Installation
============
::

  $ pear channel-discover pear.nrdev.de
  $ pear install nr/deployrst-alpha

=====
Setup
=====
::

  $ cp `pear config-get cfg_dir`/DeployRst/config.php.dist ~/.config/deploy-rst
  $ emacs ~/.config/deploy-rst
  .. change user and password


======================
Meta data in rST files
======================
It is possible to embed wiki target meta data in the ``.rst`` files directly,
so that you don't have to pass all parameters via command line.

Example::

  .. meta::
     :deploy-target: confluence
     :confluence-host: http://confluence.example.org
     :confluence-space: IT
     :confluence-page: rstpagetest

=====
Usage
=====
Deployment with meta data in the ``.rst`` file::

  $ ./deploy-rst.php README.rst

Without embedded meta data::

  $ deploy-rst --driver=confluence --confluence-host=http://confluence.example.org\
    --confluence-space=IT --confluence-page=rstpagetest README.rst

Help::

  $ ./deploy-rst.php --help


============
Dependencies
============
* rst2confluence__
* `Confluence Command line interface`__
* ``System`` from PEAR
* ``Console_CommandLine`` from PEAR

__ https://github.com/netresearch/rst2confluence
__ https://bobswift.atlassian.net/wiki/display/CSOAP/Confluence+Command+Line+Interface
