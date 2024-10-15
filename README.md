# Order of Mass Plugin

WordPress plugin from the Order of Mass package.

It adds the following functionality:

- Labels editor in the WP Administration
- A Gutenberg Block that acts as a container and shows its content only on specific weekdays
- Three shortcodes for special content (commands, labels, Bible references)

## Installation

Requirements:

- WordPress 6.6
- PHP 8.2
- Composer 2
- Git

```sh
cd /path/to/wordpress/wp-content/plugins
git clone https://github.com/tommander/oomplugin.git
cd oomplugin
composer install
```

I strongly recommend two plugins to use along with this plugin - [Essential Blocks](https://essential-blocks.com/) and [Font Awesome](https://docs.fontawesome.com/web/use-with/wordpress).

## Usage

1. In WP Administration, go to Settings > OoM Labels Settings
    1. Add languages in the section "Languages" by clicking on "Add row", filling in the language code and clicking on "Save Changes"
    2. Add translations for labels shown in English in the left-most fields
2. Add two empty pages named "Mass" (URL `/mass/`) and "Rosary" (URL `/rosary/`) and mark them both as "Virtual Page"
3. For each virtual page add child pages (one per language) that will serve as the actual content. URL should be the language code and name should be a translation of "Mass" or "Rosary" respectively.
    1. One of these child pages should be set as front page

As for the child pages that contain the actual order of mass / rosary guide pages, I will later share some more detailed guide on how to build them + some examples and templates. For now you need to know it's just paragraphs and headings that occasionally use the conditional block (e.g. for the Lord's Prayer), shortcodes (for commands like "Sitting", "Kneeling", "Standing"; for translatable labels e.g. in headings; for automatic daily Bible references based on the standard Lectionary) or advanced tabs (where more options can be chosen, e.g. for the Creed).

Please note that at this moment the plugin really expects you use the [OoM Theme](https://github.com/tommander/oomtheme) to make things look... human-friendly, let's say.

If you use another theme (which is possible, because the plugin does not depend on the OoM Theme), you might need to adjust its styles somehow.

## QA

Code is checked by `php_codesniffer` and `psalm` on every push that updates PHP/Composer/GH Workflow files.

It is recommended to run `composer qa` before pushing to the repo.

## Documentation

Under construction.

## License

[MIT License](LICENSE)
