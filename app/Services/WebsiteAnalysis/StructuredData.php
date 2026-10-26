<?php

namespace App\Services\WebsiteAnalysis;

/**
 * A page's JSON-LD structured data, parsed once: every node (including
 * @graph members and nested objects) and the questions the checks ask of it
 * — which rich results the page qualifies for, Organization sameAs, dates,
 * author, address/telephone, ratings.
 */
class StructuredData
{
    /** Organization and its common subtypes (LocalBusiness, stores, practices…). */
    private const ORGANIZATION_PATTERN = '/^(Organization|Corporation|LocalBusiness|NGO|EducationalOrganization|MedicalOrganization|NewsMediaOrganization|OnlineStore|OnlineBusiness)$|Store$|Business$|Restaurant$|Dentist$|Physician$|Attorney$|Hotel$|Agency$|Clinic$/';

    /** @var list<array> every JSON-LD object on the page */
    private array $nodes = [];

    /** Number of <script type="application/ld+json"> blocks. */
    public readonly int $blockCount;

    public function __construct(\DOMXPath $xpath)
    {
        $blocks = $xpath->query("//script[@type='application/ld+json']");
        $this->blockCount = $blocks->length;

        foreach ($blocks as $script) {
            $data = json_decode(trim($script->textContent), true);
            if (is_array($data)) {
                $this->collect($data);
            }
        }
    }

    /** @return string[] distinct @type values */
    public function types(): array
    {
        $types = [];
        foreach ($this->nodes as $node) {
            foreach ((array) ($node['@type'] ?? []) as $type) {
                if (is_string($type)) {
                    $types[] = $type;
                }
            }
        }

        return array_values(array_unique($types));
    }

    public function hasType(string ...$wanted): bool
    {
        return array_intersect($wanted, $this->types()) !== [];
    }

    /**
     * Rich results the page's markup makes it eligible for, in a fixed order:
     * Product, Review stars, FAQ, Breadcrumb, Organization, Course, Event.
     *
     * @return string[]
     */
    public function richResultTypes(): array
    {
        $eligible = [];
        if ($this->hasType('Product', 'ProductGroup')) {
            $eligible[] = 'Product';
        }
        if ($this->hasRating()) {
            $eligible[] = 'Review stars';
        }
        if ($this->hasType('FAQPage')) {
            $eligible[] = 'FAQ';
        }
        if ($this->hasType('BreadcrumbList')) {
            $eligible[] = 'Breadcrumb';
        }
        if ($this->organizations() !== []) {
            $eligible[] = 'Organization';
        }
        if ($this->hasType('Course', 'CourseInstance')) {
            $eligible[] = 'Course';
        }
        if ($this->hasType('Event', 'EducationEvent', 'BusinessEvent', 'SocialEvent')) {
            $eligible[] = 'Event';
        }

        return $eligible;
    }

    /** Review / AggregateRating as a node, or as a property of another node. */
    public function hasRating(): bool
    {
        if ($this->hasType('Review', 'AggregateRating')) {
            return true;
        }
        foreach ($this->nodes as $node) {
            if (isset($node['aggregateRating']) || isset($node['review'])) {
                return true;
            }
        }

        return false;
    }

    /** @return list<array> Organization / LocalBusiness (and subtype) nodes */
    public function organizations(): array
    {
        return array_values(array_filter($this->nodes, function ($node) {
            foreach ((array) ($node['@type'] ?? []) as $type) {
                if (is_string($type) && preg_match(self::ORGANIZATION_PATTERN, $type)) {
                    return true;
                }
            }

            return false;
        }));
    }

    /** True when an Organization node lists at least one sameAs profile. */
    public function organizationHasSameAs(): bool
    {
        foreach ($this->organizations() as $org) {
            if (array_filter((array) ($org['sameAs'] ?? [])) !== []) {
                return true;
            }
        }

        return false;
    }

    /** Latest of dateModified / datePublished found, as given (ISO 8601), or null. */
    public function latestDate(): ?string
    {
        $dates = [];
        foreach ($this->nodes as $node) {
            foreach (['dateModified', 'datePublished'] as $key) {
                if (is_string($node[$key] ?? null) && $node[$key] !== '') {
                    $dates[] = $node[$key];
                }
            }
        }
        rsort($dates);

        return $dates[0] ?? null;
    }

    public function hasAuthor(): bool
    {
        foreach ($this->nodes as $node) {
            if (!empty($node['author'])) {
                return true;
            }
        }

        return false;
    }

    public function hasArticle(): bool
    {
        return $this->hasType('Article', 'BlogPosting', 'NewsArticle', 'TechArticle', 'Report');
    }

    public function hasTelephone(): bool
    {
        foreach ($this->nodes as $node) {
            if (!empty($node['telephone'])) {
                return true;
            }
        }

        return false;
    }

    public function hasAddress(): bool
    {
        foreach ($this->nodes as $node) {
            if (!empty($node['address'])) {
                return true;
            }
        }

        return $this->hasType('PostalAddress');
    }

    private function collect(array $data): void
    {
        if (array_is_list($data)) {
            foreach ($data as $item) {
                if (is_array($item)) {
                    $this->collect($item);
                }
            }

            return;
        }

        $this->nodes[] = $data;
        foreach ($data as $value) {
            if (is_array($value)) {
                $this->collect($value);
            }
        }
    }
}
