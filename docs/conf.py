# Configuration file for the Sphinx documentation builder.
#
# For the full list of built-in configuration values, see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Project information -----------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#project-information

project = 'Apiary'
copyright = '2023 RoboJackets, Inc.'
author = 'Kristaps Berzinch, Evan Strat, Joshua Oldenburg'

# -- General configuration ---------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#general-configuration

extensions = ["sphinx.ext.todo", "sphinx.ext.extlinks"]

templates_path = ['_templates']
exclude_patterns = ['_build', 'Thumbs.db', '.DS_Store']

todo_include_todos = True

nitpicky = True

extlinks = {
    'slack': ('https://robojackets.slack.com/app_redirect?channel=%s', '#%s')
}
extlinks_detect_hardcoded_links = True

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
}
