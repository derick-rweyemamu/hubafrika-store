<x-mail::message>
# {{ $campaign->subject }}

{!! \App\Support\RichText::toHtml($campaign->content) !!}

---

<x-mail::subcopy>
You are receiving this email because you subscribed to updates from **{{ $siteName }}**.

If you no longer wish to receive these emails, you can [unsubscribe here]({{ $unsubscribeUrl }}).
</x-mail::subcopy>
</x-mail::message>
