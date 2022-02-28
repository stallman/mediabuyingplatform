<?php

namespace App\Twig;

use App\Entity\Country;
use App\Entity\CurrencyList;
use App\Entity\EntityInterface;
use App\Entity\Image;
use App\Entity\News;
use App\Entity\NewsCategory;
use App\Service\CronHistoryChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DashboardExtension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('render_side_bar', [$this, 'renderSideBar']),
            new TwigFunction('get_table_columns', [$this, 'getTableColumns']),
            new TwigFunction('call_entity_getter', [$this, 'callEntityGetter']),
            new TwigFunction('get_list_config', [$this, 'getListConfig']),
            new TwigFunction('render_admin_nav_bar', [$this, 'renderNavBar']),
            new TwigFunction('render_footer', [$this, 'renderFooter']),
            new TwigFunction('news_categories_as_string', [$this, 'newsCategoriesAsString']),
            new TwigFunction('news_countries_as_string', [$this, 'newsCountriesAsString']),
            new TwigFunction('render_counter_labels', [$this, 'renderCounterLabels']),
            new TwigFunction('render_bulk_checkbox', [$this, 'renderBulkCheckbox']),
            new TwigFunction('render_bulk_item_checkbox', [$this, 'renderBulkItemCheckbox']),
            new TwigFunction('render_bulk_item_checkbox_without_rules', [$this, 'renderBulkItemCheckboxWithoutRules']),
            new TwigFunction('render_bulk_item_checkbox_as_form', [$this, 'renderBulkItemCheckboxAsForm']),
            new TwigFunction('render_bulk_item_checkbox_black_list', [$this, 'renderBulkActionSelectorBlackList']),
            new TwigFunction('get_image_preview', [$this, 'getImagePreview']),
            new TwigFunction('get_images_preview', [$this, 'getImagesPreview']),
            new TwigFunction('translate_news_type', [$this, 'translateNewsType']),
            new TwigFunction('render_bulk_action_selector', [$this, 'renderBulkActionSelector']),
            new TwigFunction('render_teasers_group_action_selector', [$this, 'renderTeasersGroupActionSelector']),
            new TwigFunction('render_bulk_action_selector_as_form', [$this, 'renderBulkActionSelectorAsForm']),
            new TwigFunction('render_news_sources_list_modal', [$this, 'renderNewsSourcesListModal']),
            new TwigFunction('render_teasers_geo_list', [$this, 'renderTeasersGeoList']),
            new TwigFunction('convert_to_user_currency', [$this, 'convertToUserCurrency']),
            new TwigFunction('render_user_currency_information', [$this, 'renderUserCurrencyInformation']),
            new TwigFunction('render_update_chron_data_info', [$this, 'renderUpdateCronDateInfo']),
            new TwigFunction('render_timezone', [$this, 'renderTimezone']),
        ];
    }

    public function getFilters() {
        return array(
            new TwigFilter('custom_slice', array($this, 'customSlice'))
        );
    }

    public function renderUpdateCronDateInfo($user, $cronSlug)
    {
        $userCurrency = $this->currencyConverter->getUserCurrency($user);
        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $lastCronTime = $cronHistoryChecker->getLastCronTime($cronSlug);

        return $this->twigEnvironment->render('dashboard/partials/update_cron_date_info.html.twig', [
            'user_currency' => $userCurrency,
            'cron_date' => $lastCronTime,
        ]);
    }

    /**
     * @param UserInterface $user
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderUserCurrencyInformation(UserInterface $user)
    {
        $userCurrency = $this->currencyConverter->getUserCurrency($user);

        return $this->twigEnvironment->render('dashboard/partials/user_currency_information.html.twig', [
            'user_currency' => $userCurrency
        ]);
    }

    /**
     * @param float $price
     * @param UserInterface $user
     * @param CurrencyList|null $ruble
     * @return float
     */
    public function convertToUserCurrency(float $price, UserInterface $user, ?CurrencyList $ruble = null)
    {
        return $this->currencyConverter->convertRubleToUserCurrency($price, $user, 4, $ruble);
    }

    /**
     * @param array $groupList
     * @param array $subgroupList
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderTeasersGroupActionSelector(array $teaserGroups = [])
    {
        return $this->twigEnvironment->render(
        'dashboard/partials/table/teasers_group_actions_selector.html.twig', [
            'teaser_groups' => $teaserGroups
        ]);
    }
    /**
     * @param string|null $deletePath
     * @param string|null $setActivePath
     * @param string|null $setDisablePath
     * @param string|null $changeTeaserSubGroup
     * @param string|null $convertPath
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBulkActionSelector(?string $deletePath = null, ?string $setActivePath = null,
                                             ?string $setDisablePath = null, ?string $changeTeaserSubGroup = null,
                                             ?string $convertPath = null)
    {
        $pathArray = [];
        if ($deletePath) $pathArray['delete_path'] = $deletePath;
        if ($convertPath) $pathArray['convert_path'] = $convertPath;
        if ($setActivePath) $pathArray['set_active_path'] = $setActivePath;
        if ($setDisablePath) $pathArray['set_disable_path'] = $setDisablePath;
        if ($changeTeaserSubGroup) $pathArray['change_teaser_subgroup'] = $changeTeaserSubGroup;

        return $this->twigEnvironment->render('dashboard/partials/table/bulk_actions_selector.html.twig', $pathArray);
    }

    /**
     * @param string $deletePath
     * @param string $setActivePath
     * @param string $setDisablePath
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBulkActionSelectorAsForm(string $deletePath = null, string $setActivePath = null, string $setDisablePath = null)
    {
        $pathArray = [];
        if ($deletePath) $pathArray['delete_path'] = $deletePath;
        if ($setActivePath) $pathArray['set_active_path'] = $setActivePath;
        if ($setDisablePath) $pathArray['set_disable_path'] = $setDisablePath;

        return $this->twigEnvironment->render('dashboard/partials/table/bulk_actions_selector_as_form.html.twig', $pathArray);
    }

    public function renderBulkActionSelectorBlackList(string $addToBlack = null, string $addToWhite = null, string $removeFromBlack = null, string $removeFromWhite = null)
    {
        $pathArray = [];
        if ($addToBlack) $pathArray['add_to_black_path'] = $addToBlack;
        if ($addToWhite) $pathArray['add_to_white_path'] = $addToWhite;
        if ($removeFromBlack) $pathArray['remove_from_black_path'] = $removeFromBlack;
        if ($removeFromWhite) $pathArray['remove_from_white_path'] = $removeFromWhite;

        return $this->twigEnvironment->render('dashboard/partials/table/bulk_actions_selector_black_list.html.twig', $pathArray);
    }

    /**
     * @param EntityInterface $entity
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getImagePreview(EntityInterface $entity)
    {
        /** @var Image $image */
        $image = $this->entityManager->getRepository(Image::class)->getEntityImage($entity);
        $imagePath = null;

        if ($image) {
            $imagePath = $image->getFullCropImagePath();
        }

        return $this->twigEnvironment->render('dashboard/partials/table/image_preview.html.twig', [
            'image_path' => $imagePath,
        ]);
    }

    /**
     * @param string $newsType
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function translateNewsType(string $newsType)
    {
        if($newsType == 'own') $newsType = 'Собственная';
        if($newsType == 'common') $newsType = 'Общедоступная';

        return $newsType;
    }

    /**
     * @param EntityInterface $entity
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getImagesPreview(EntityInterface $entity)
    {
        /** @var Image $image */
        $image = $this->entityManager->getRepository(Image::class)->getEntityImage($entity);
        $imagesPath = null;
        if($image){
            $imagesPath['Cropped'] = $image->getFullCropImagePath();
            $imagesPath['Original'] = $image->getFullImagePath();
        }

        return $this->twigEnvironment->render('dashboard/partials/table/images_preview.html.twig', [
            'images' => $imagesPath,
        ]);
    }

    /**
     * @param string $routName
     * @param EntityInterface $item
     * @return string|void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBulkItemCheckbox(string $routName, EntityInterface $item)
    {
        $config = $this->getConfigBySection($routName);
        if (isset($config['bulk_action_checkbox']) && $config['bulk_action_checkbox']) {
            return $this->twigEnvironment->render('dashboard/partials/table/bulk_item_checkbox.html.html.twig', [
                'item_id' => $item->getId(),
                'item' => $item
            ]);
        }

        return;
    }

    /**
     * @param string $routName
     * @param EntityInterface $item
     * @return string|void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBulkItemCheckboxWithoutRules(string $routName, EntityInterface $item)
    {
        $config = $this->getConfigBySection($routName);
        if (isset($config['bulk_action_checkbox']) && $config['bulk_action_checkbox']) {
            return $this->twigEnvironment->render('dashboard/partials/table/bulk_item_checkbox_without_rules.html.twig', [
                'item_id' => $item->getId(),
                'item' => $item
            ]);
        }

        return;
    }

    /**
     * @param string $routName
     * @param EntityInterface $item
     * @return string|void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBulkItemCheckboxAsForm(string $routName, EntityInterface $item)
    {
        $config = $this->getConfigBySection($routName);
        if (isset($config['bulk_action_checkbox']) && $config['bulk_action_checkbox']) {
            return $this->twigEnvironment->render('dashboard/partials/table/bulk_item_checkbox_as_form.html.twig', [
                'item_id' => $item->getId(),
                'item' => $item,
                'item_class' => get_class($item)
            ]);
        }

        return;
    }

    /**
     * @param string $routName
     * @return string|void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBulkCheckbox(string $routName)
    {
        $config = $this->getConfigBySection($routName);

        if (isset($config['bulk_action_checkbox']) && $config['bulk_action_checkbox']) {
            return $this->twigEnvironment->render('dashboard/partials/table/bulk_actions_checkbox.html.twig', []);
        }

        return;
    }

    public function renderCounterLabels()
    {
        return $this->twigEnvironment->render('dashboard/partials/counter_labels.html.twig', []);
    }

    /**
     * @param News $news
     * @return string
     */
    public function newsCategoriesAsString(News $news)
    {
        $categories = $news->getCategories()->toArray();
        $categoryTitles = [];

        /** @var NewsCategory $category */
        foreach ($categories as $category) {
            $categoryTitles[] = $category;
        }

        return implode(', ', $categoryTitles);
    }

    /**
     * @param News $news
     * @return string
     */

    public function newsCountriesAsString(News $news)
    {
        $countries = $news->getCountries()->toArray();
        $countryTitles = [];

        /** @var Country $country */
        foreach ($countries as $country) {
            $countryTitles[] = $country->getName();
        }

        return implode(', ', $countryTitles);
    }

    /**
     * @param string $routName
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderFooter(string $routName)
    {
        $footerConfig = $this->getConfig($routName)['footer'];

        return $this->twigEnvironment->render('dashboard/partials/footer.html.twig', [
            'footer_config' => $footerConfig,
        ]);
    }

    /**
     * @param string $routName
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderNavBar(string $routName)
    {
        $navBarConfig = $this->getConfig($routName)['navbar'];

        return $this->twigEnvironment->render('dashboard/partials/navbar.html.twig', [
            'nav_bar_config' => $navBarConfig,
        ]);
    }

    /**
     * @param string $routName
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderSideBar(string $routName)
    {
        $config = $this->getConfig($routName);

        return $this->twigEnvironment->render('dashboard/partials/main_side_bar.html.twig', [
            'sidebar' => $config['sidebar'],
            'common' => $config['common']
        ]);
    }

    /**
     * @param string $entityFQN
     * @param string $dashboard
     * @return array
     * @throws \ReflectionException
     */
    public function getTableColumns(string $entityFQN, string $dashboard)
    {
        $reflection = new \ReflectionClass($entityFQN);
        $properties = $reflection->getProperties();
        $propertyFields = [];
        $userFields = [];

        for ($i = 0; $i < count($properties); $i++) {
            $propertyFields[$i]['name'] = $properties[$i]->name;
            $propertyFields[$i]['title'] = $properties[$i]->name;
        }

        $config = $this->getConfig($dashboard);

        foreach ($config as $key => $value) {
            if ($value['entity'] == $entityFQN) {
                if (isset($value['list']['fields']) && is_array($value['list']['fields']) && !empty($value['list']['fields'])) {
                    for ($i = 0; $i < count($value['list']['fields']); $i++) {
                        $userField = $value['list']['fields'][$i]['name'];
                        $userTitle = $value['list']['fields'][$i]['title'];

                        $userFields[$i]['name'] = $userField;
                        $userFields[$i]['title'] = $userTitle;
                    }
                }
            }
        }

        return empty($userFields) ? $propertyFields : $userFields;
    }

    /**
     * @param string $entityFQN
     * @param string $dashboard
     * @return array|mixed
     */
    public function getListConfig(string $entityFQN, string $dashboard)
    {
        $config = $this->getConfig($dashboard);
        $listConfig = [];

        foreach ($config as $key => $value) {
            if ($value['entity'] == $entityFQN) {
                $listConfig = $value['list'];
            }
        }

        return $listConfig;
    }

    public function callEntityGetter($entity, string $column)
    {
        $getter = 'get' . ucfirst(trim($column));
        $result = call_user_func_array([$entity, $getter], []);

        return is_array($result) ? json_encode($result) : $result;
    }

    private function parseConfig(array $config)
    {
        $sidebarItems = [];

        foreach ($config as $key => $value) {
            if (isset($value['sidebar']) && is_array($value['sidebar']) && !empty($value['sidebar'])) {
                $sidebarItems[$key] = $value['sidebar'];
            }
            $sidebarItems[$key]['slug'] = $value['slug'];
        }

        return $sidebarItems;
    }

    public function renderNewsSourcesListModal()
    {
        return $this->twigEnvironment->render('dashboard/partials/news_sources_list_modal.html.twig', []);
    }

    public function customSlice($input, $from, $to=null)
    {
        if (!$to) {
            $to = $from;
            $from = 0;
        }

        $to = $this->getCutPosition($input, $to);
        return mb_substr($input, $from, $to);
    }


    private function getAllComments($string)
    {
        preg_match_all("/\[teaser block]/", $string, $matches);
        return $matches[0];
    }

    private function getCommentStartAndEndPositions($input, $comment)
    {
        $startPos = strpos($input, $comment);
        $endPos = $startPos + mb_strlen($comment);
        return ['startPos' => $startPos, 'endPos' => $endPos];
    }

    private function getCutPosition($input, $to) {
        $comments = $this->getAllComments($input);
        foreach ($comments as $comment) {
            $positions = $this->getCommentStartAndEndPositions($input, $comment);
            if (($positions['startPos'] < $to) && ($to < $positions['endPos'])) {
                //срез внутри комментария
                $to = $positions['endPos'];
                break;
            }
        }
        return $to;
    }
    
    public function renderTeasersGeoList($teaser) {
        $countries = [];
        $settings = $teaser->getTeasersSubGroup()->getTeasersSubGroupSettings();
        
        if ($settings->count() > 0) {
            foreach ($settings as $setting) {
                if ($setting->getGeoCode()) {
                    $countries[] = $setting->getGeoCode()->getName();
                }
            }

            if (count($countries) > 0) {
                return implode(", ", $countries);
            }
            
        }

        return "Все";
    }

    public function renderTimezone()
    {
        return date_default_timezone_get();
    }
}