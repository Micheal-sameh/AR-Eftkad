@extends('layouts.guest')

@section('title', 'Delete your account / حذف الحساب - Eftkad')

@section('content')
@php
    $email = config("app.support_email");
    $linkEn = $email ? "<a class=\"text-link\" href=\"mailto:".e($email)."\">".e($email)."</a>" : "your service administrator";
    $linkAr = $email ? "<a class=\"text-link\" href=\"mailto:".e($email)."\">".e($email)."</a>" : "مسؤول الخدمة";
@endphp
<div style="line-height:1.8;font-size:.95rem">
<div dir="ltr" lang="en">
    <h2 class="page-title" style="font-size:1.2rem">Delete your Eftkad account and data</h2>
    <p>Eftkad accounts are managed through Avarewase SSO. To delete your account and the data linked to it:</p>
    <ol>
        <li>Send a request to {!! $linkEn !!} from the email address of your account, with the subject "Delete my Eftkad account".</li>
        <li>We verify the request and delete your account and personal data from Eftkad, normally within 30 days.</li>
    </ol>
    <p><b>What is deleted:</b> your profile (name, email, user ID, role) and your sign-in link to Eftkad.</p>
    <p><b>What may be kept:</b> visit records you created may be kept, with your name removed, so the service group's pastoral history is not lost. Records that we are required to keep by law are kept only as long as required.</p>
    <p>You can also ask to delete only some data without deleting the account, using the same email.</p>
</div>

<hr style="margin:1.5rem 0;opacity:.3">

<div dir="rtl" lang="ar">
    <h2 class="page-title" style="font-size:1.2rem">حذف حساب إفتقاد وبياناته</h2>
    <p>تُدار حسابات إفتقاد عبر خدمة Avarewase SSO. لحذف حسابك والبيانات المرتبطة به:</p>
    <ol>
        <li>أرسل طلباً إلى {!! $linkAr !!} من البريد الإلكتروني الخاص بحسابك، بعنوان "حذف حسابي في إفتقاد".</li>
        <li>نتحقق من الطلب ونحذف حسابك وبياناتك الشخصية من إفتقاد، عادةً خلال 30 يوماً.</li>
    </ol>
    <p><b>ما يُحذف:</b> ملفك الشخصي (الاسم والبريد ومعرّف المستخدم والدور) وربط تسجيل الدخول بإفتقاد.</p>
    <p><b>ما قد يبقى:</b> قد تبقى سجلات الافتقاد التي أنشأتها بعد إزالة اسمك منها حتى لا يضيع التاريخ الرعوي للخدمة، وتُحفظ السجلات المطلوبة قانوناً للمدة المطلوبة فقط.</p>
    <p>يمكنك أيضاً طلب حذف بعض البيانات فقط دون حذف الحساب بنفس الطريقة.</p>
</div>
</div>

<a class="text-link" href="/login" style="display:block;margin-top:1.2rem">Back to sign in / العودة لتسجيل الدخول</a>
@endsection
