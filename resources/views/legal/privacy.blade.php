@extends('layouts.guest')

@section('title', 'Privacy Policy / سياسة الخصوصية - Eftkad')

@section('content')
@php($email = config('app.support_email'))
<div style="line-height:1.8;font-size:.95rem">
<div dir="ltr" lang="en">
    <h2 class="page-title" style="font-size:1.2rem">Privacy Policy</h2>
    <p>Eftkad is a pastoral visit management app used by authorised servants. This policy explains what the app collects and how it is used. Last updated: November 2026.</p>

    <h3>Sign-in</h3>
    <p>You sign in through Avarewase SSO (auth.avarewase.com). We receive your name, email address and a user ID from that service. We never see or store your password.</p>

    <h3>Information the app handles</h3>
    <ul>
        <li><b>Account details:</b> name, email, user ID, membership code, phone number, date of birth, profile picture and your role in the app.</li>
        <li><b>Visit records (about the people visited):</b> information that authorised servants enter about the person visited, such as their name or membership code, correspondence address, contact means, needs, mass and meeting attendance, confession details (including the confession father), notes and follow-ups, and a location link. Some of this is religious information and is treated as sensitive: it is visible only to the assigned father and servant, people the visit is shared with, and service managers.</li>
        <li><b>Location:</b> only when you tap "Use current location" on the visit form. It is attached to that visit and is not tracked in the background.</li>
        <li><b>Biometrics:</b> if you turn on the app lock, fingerprint or face unlock is handled by your device. The app only receives a yes/no result; no biometric data is stored or sent.</li>
    </ul>

    <h3>How it is used</h3>
    <p>Data is used only to run the visit management service for your church service group. We do not sell data, show ads or share it with advertisers. Data is shared only with authorised users of the app according to their role, and with the hosting and sign-in providers needed to run the service.</p>

    <h3>Security and retention</h3>
    <p>All traffic is encrypted with HTTPS. Account data is kept while the account is active and deleted within 30 days of a verified deletion request. Visit records are kept for as long as the service group needs its pastoral history, and are removed or anonymised on request of the person they concern.</p>

    <h3>Your choices and deletion</h3>
    <p>You can ask to access, correct or delete your data and account at any time. See <a class="text-link" href="/account-deletion">how to delete your account</a>.</p>

    <h3>Contact</h3>
    <p>@if($email)<a class="text-link" href="mailto:{{ $email }}">{{ $email }}</a>@else Contact your service administrator.@endif</p>
</div>

<hr style="margin:1.5rem 0;opacity:.3">

<div dir="rtl" lang="ar">
    <h2 class="page-title" style="font-size:1.2rem">سياسة الخصوصية</h2>
    <p>تطبيق إفتقاد لإدارة الافتقاد الرعوي يستخدمه الخدام المصرّح لهم. توضح هذه السياسة ما يجمعه التطبيق وكيف يُستخدم. آخر تحديث: 11/2026.</p>

    <h3>تسجيل الدخول</h3>
    <p>يتم تسجيل الدخول عبر خدمة Avarewase SSO ‏(auth.avarewase.com). نستلم منها اسمك وبريدك الإلكتروني ومعرّف المستخدم، ولا نرى كلمة المرور ولا نخزنها.</p>

    <h3>المعلومات التي يتعامل معها التطبيق</h3>
    <ul>
        <li><b>بيانات الحساب:</b> الاسم والبريد الإلكتروني ومعرّف المستخدم وكود العضوية ورقم الهاتف وتاريخ الميلاد والصورة الشخصية ودورك في التطبيق.</li>
        <li><b>سجلات الافتقاد (عن الأشخاص المفتقَدين):</b> معلومات يدخلها الخدام المصرّح لهم عن المفتقَد، مثل الاسم أو كود العضوية وعنوان المراسلة ووسائل التواصل والاحتياجات والحضور في القداس والاجتماعات وبيانات الاعتراف (ومنها أب الاعتراف) والملاحظات والمتابعة ورابط الموقع. بعض هذه البيانات ديني وحساس، ولا يطّلع عليها إلا الأب والخادم المسؤولان ومن شُورك معهم الافتقاد ومديرو الخدمة.</li>
        <li><b>الموقع:</b> فقط عند الضغط على "استخدام موقعي الحالي" في نموذج الافتقاد، ويُرفق بذلك الافتقاد ولا يتم تتبعك في الخلفية.</li>
        <li><b>البصمة:</b> عند تفعيل قفل التطبيق، يتم التحقق بالبصمة أو الوجه عبر جهازك، ويستلم التطبيق نتيجة (نجاح/فشل) فقط دون تخزين أو إرسال أي بيانات حيوية.</li>
    </ul>

    <h3>كيف تُستخدم البيانات</h3>
    <p>تُستخدم البيانات فقط لتشغيل خدمة إدارة الافتقاد لخدمتك. لا نبيع البيانات ولا نعرض إعلانات. تُتاح البيانات للمستخدمين المصرّح لهم فقط حسب أدوارهم، ولمزوّدي الاستضافة وتسجيل الدخول اللازمين لتشغيل الخدمة.</p>

    <h3>الأمان والاحتفاظ بالبيانات</h3>
    <p>جميع الاتصالات مشفرة عبر HTTPS. تُحفظ بيانات الحساب طالما الحساب نشط وتُحذف خلال 30 يوماً من طلب حذف تم التحقق منه. وتُحفظ سجلات الافتقاد بقدر حاجة الخدمة للتاريخ الرعوي، وتُحذف أو يُزال عنها التعريف بناءً على طلب الشخص المعني.</p>

    <h3>خياراتك وحذف الحساب</h3>
    <p>يمكنك في أي وقت طلب الاطلاع على بياناتك أو تصحيحها أو حذفها وحذف حسابك. راجع <a class="text-link" href="/account-deletion">كيفية حذف الحساب</a>.</p>

    <h3>التواصل</h3>
    <p>@if($email)<a class="text-link" href="mailto:{{ $email }}">{{ $email }}</a>@else تواصل مع مسؤول الخدمة.@endif</p>
</div>
</div>

<a class="text-link" href="/login" style="display:block;margin-top:1.2rem">Back to sign in / العودة لتسجيل الدخول</a>
@endsection
