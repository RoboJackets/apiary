# Configuration file for the Sphinx documentation builder.
#
# For the full list of built-in configuration values, see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Project information -----------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#project-information

project = 'Apiary'
copyright = '2023 RoboJackets, Inc.'
author = 'Kristaps Berzinch, Evan Strat, Josh Oldenburg'

# -- General configuration ---------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#general-configuration

extensions = [
    "sphinx.ext.todo",
    "sphinx.ext.extlinks",
    "sphinx.ext.autosectionlabel",
    "sphinxext.opengraph",
    "sphinxcontrib.httpdomain",
]

templates_path = ['_templates']
exclude_patterns = ['_build', 'Thumbs.db', '.DS_Store']

todo_include_todos = True

nitpicky = True

extlinks = {
    'slack': ('https://robojackets.slack.com/app_redirect?channel=%s', '#%s')
}
extlinks_detect_hardcoded_links = True

ogp_site_url = "/docs/"
ogp_social_cards = {
    "enable": False
}
ogp_use_first_image = False
ogp_site_name = "Apiary Documentation"
ogp_enable_meta_description = False

# -- Options for HTML output -------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#options-for-html-output

html_theme = "furo"
html_static_path = ['_static']
html_title = "Apiary"
html_baseurl = '/docs/'
html_theme_options = {
    "source_repository": "https://github.com/RoboJackets/apiary/",
    "source_branch": "main",
    "source_directory": "docs/",
    "footer_icons": [
        {
            "name": "Slack",
            "url": "https://robojackets.slack.com/app_redirect?channel=apiary",
            "html": """
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.1" viewBox="0 0 122.8 122.8" xmlns="http://www.w3.org/2000/svg" style="padding-right: 0.25rem">
 <g transform="translate(-73.6,-73.6)">
  <path d="m99.4 151.2c0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9 5.8-12.9 12.9-12.9h12.9z"/>
  <path d="m105.9 151.2c0-7.1 5.8-12.9 12.9-12.9s12.9 5.8 12.9 12.9v32.3c0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9v-32.3z"/>
  <path d="m118.8 99.4c-7.1 0-12.9-5.8-12.9-12.9s5.8-12.9 12.9-12.9 12.9 5.8 12.9 12.9v12.9z"/>
  <path d="m118.8 105.9c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9h-32.3c-7.1 0-12.9-5.8-12.9-12.9s5.8-12.9 12.9-12.9h32.3z"/>
  <path d="m170.6 118.8c0-7.1 5.8-12.9 12.9-12.9s12.9 5.8 12.9 12.9-5.8 12.9-12.9 12.9h-12.9z"/>
  <path d="m164.1 118.8c0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9v-32.3c0-7.1 5.8-12.9 12.9-12.9s12.9 5.8 12.9 12.9z"/>
  <path d="m151.2 170.6c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9-12.9-5.8-12.9-12.9v-12.9z"/>
  <path d="m151.2 164.1c-7.1 0-12.9-5.8-12.9-12.9s5.8-12.9 12.9-12.9h32.3c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9z"/>
 </g>
</svg>
            """,
            "class": "",
        },
        {
            "name": "GitHub",
            "url": "https://github.com/RoboJackets/apiary",
            "html": """
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path>
                </svg>
            """,
            "class": "",
        },
    ],
}

rst_prolog = """
.. |actionsmenu| raw:: html

   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -5 20 20" fill="currentColor" width="20" height="20" class="inline" role="presentation"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 0 14 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>

.. |editicon| raw:: html

   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="3 -3 17 24" stroke="currentColor" width="20" height="18" class="inline-block" role="presentation"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
"""

http_strict_mode = True
