<?php

namespace Nwidart\Modules\Process;

class Updater extends Runner
{
    /**
     * Update the dependencies for the specified module by given the module name.
     *
     * @param string $module
     */
    public function update($module)
    {
        $module = $this->module->findOrFail($module);

        //scripts
        $scripts = $module->getComposerAttr('scripts', []);
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        foreach ($scripts as $key => $script):
            $composer['scripts'][$key] = array_unique(array_merge($composer['scripts'][$key], $script));
        endforeach;

        file_put_contents(base_path('composer.json'), json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $packages = $module->getComposerAttr('require', []);

        chdir(base_path());

        foreach ($packages as $name => $version) {
            $package = "\"{$name}:{$version}\"";

            $this->run("composer require {$package}");
        }

        //require-dev
        $packages_dev = $module->getComposerAttr('require-dev', []);

        foreach ($packages_dev as $name => $version) {
            $package = "\"{$name}:{$version}\"";

            $this->run("composer require --dev {$package}");
        }
    }
}
