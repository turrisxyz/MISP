<?php
    $htmlElements = [];
    if (!empty($event['Feed'])) {
        foreach ($event['Feed'] as $relatedFeed) {
            $relatedData = [
                __('Name') => $relatedFeed['name'],
                __('URL') => $relatedFeed['url'],
                __('Provider') => $relatedFeed['provider'],
            ];
            $popover = '';
            foreach ($relatedData as $k => $v) {
                $popover .= sprintf(
                    '<span class="bold">%s</span>: <span class="blue">%s</span><br>',
                    h($k),
                    h($v)
                );
            }
            if ($relatedFeed ['source_format'] === 'misp') {
                $htmlElements[] = sprintf(
                    '<form action="%s/feeds/previewIndex/%s" method="post" style="margin:0px;">%s</form>',
                    h($baseurl),
                    h($relatedFeed['id']),
                    sprintf(
                        '<input type="hidden" name="data[Feed][eventid]" value="%s">
                        <input type="submit" class="linkButton useCursorPointer" value="%s" data-toggle="popover" data-content="%s" data-trigger="hover">',
                        h(json_encode($relatedFeed['event_uuids'])),
                        h($relatedFeed['name']) . ' (' . $relatedFeed['id'] . ')',
                        h($popover)
                    )
                );
            } else {
                $htmlElements[] = sprintf(
                    '<a href="%s/feeds/previewIndex/%s" data-toggle="popover" data-content="%s" data-trigger="hover">%s</a><br>',
                    h($baseurl),
                    h($relatedFeed['id']),
                    h($popover),
                    h($relatedFeed['name']) . ' (' . $relatedFeed['id'] . ')'
                );

            }
        }
    } else {
        $htmlElements[] = sprintf(
            '<span>%s</span>',
            __(
                'This event has %s correlations with data contained within the various feeds, however, due to the large number of attributes the actual feed correlations are not shown. Click <a href="%s\/overrideLimit:1">here</a> to refresh the page with the feed data loaded.',
                h($event['Event']['FeedCount']),
                h(Router::url(null, true))
            )
        );
    }

    $total = count($event['RelatedEvent']);
    foreach ($event['RelatedEvent'] as $relatedEvent) {
        $count++;
        if ($count == $display_threshold+1 && $total > $display_threshold) {
            $htmlElements[] = sprintf(
                '<div class="%s">%s</div>',
                'no-side-padding correlation-expand-button useCursorPointer linkButton blue',
                __('Show (%s more)', $total - ($count-1)),
            );
        }
        $htmlElements[] = $this->element('/Events/View/related_event', array(
            'related' => $relatedEvent['Event'],
            'color_red' => $relatedEvent['Event']['orgc_id'] == $me['org_id'],
            'hide' => $count > $display_threshold,
            'relatedEventCorrelationCount' => $relatedEventCorrelationCount,
            'from_id' => $event['Event']['id']
        ));
    }
    if ($total > $display_threshold) {
        $htmlElements[] = sprintf(
            '<div class="%s" style="display:none;">%s</div>',
            'no-side-padding correlation-collapse-button useCursorPointer linkButton blue',
            'display:none',
            __('Collapse…')
        );
    }

    echo sprintf(
        '<h3>%s%s</h3><div class="inline correlation-container">%s</div>',
        __('Related Feeds'),
        sprintf(
            '<a href="#attributeList" title="%s" onclick="%s">%s</a>',
            __('Show just attributes that have feed hits'),
            sprintf(
                "toggleBoolFilter('%s/events/view/%s', 'feed')",
                $baseurl,
                h($event['Event']['id'])
            ),
            __('(show)')
        ),
        implode(PHP_EOL, $htmlElements)
    );
