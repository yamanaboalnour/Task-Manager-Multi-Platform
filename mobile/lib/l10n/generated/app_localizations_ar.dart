// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Arabic (`ar`).
class AppLocalizationsAr extends AppLocalizations {
  AppLocalizationsAr([String locale = 'ar']) : super(locale);

  @override
  String get appTitle => 'إدارة المهام';

  @override
  String get loginTitle => 'تسجيل الدخول';

  @override
  String get registerTitle => 'طلب إنشاء حساب';

  @override
  String get welcomeBack => 'مرحبًا بعودتك';

  @override
  String get loginSubtitle => 'سجّل الدخول لمتابعة مهامك.';

  @override
  String get registerSubtitle => 'يراجع المدير طلبك قبل تفعيل الحساب.';

  @override
  String get apiBaseUrl => 'عنوان API الأساسي';

  @override
  String get apiBaseUrlHint => 'https://your-server.example';

  @override
  String get apiBaseUrlHelp =>
      'عنوان المحاكي على Android: http://10.0.2.2:8000';

  @override
  String get invalidApiUrl => 'أدخل عنوانًا صالحًا يبدأ بـ http:// أو https://';

  @override
  String get name => 'الاسم';

  @override
  String get firstName => 'الاسم الأول';

  @override
  String get lastName => 'اسم العائلة';

  @override
  String get nameRequired => 'الاسم مطلوب.';

  @override
  String get email => 'البريد الإلكتروني';

  @override
  String get emailRequired => 'أدخل عنوان بريد إلكتروني صالحًا.';

  @override
  String get password => 'كلمة المرور';

  @override
  String get passwordRequired => 'كلمة المرور مطلوبة.';

  @override
  String get passwordMinLength =>
      'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.';

  @override
  String get confirmPassword => 'تأكيد كلمة المرور';

  @override
  String get passwordsMismatch => 'كلمتا المرور غير متطابقتين.';

  @override
  String get createAccount => 'إرسال طلب إنشاء الحساب';

  @override
  String get requestSubmitted => 'تم إرسال طلب إنشاء الحساب للمراجعة.';

  @override
  String get forgotPassword => 'نسيت كلمة المرور؟';

  @override
  String get forgotPasswordInstructions =>
      'أدخل بريدك الإلكتروني لإرسال رابط آمن لإعادة التعيين.';

  @override
  String get resetLinkSent =>
      'إذا كان البريد مرتبطًا بحساب، فسيتم إرسال رابط إعادة التعيين.';

  @override
  String get sendResetLink => 'إرسال رابط إعادة التعيين';

  @override
  String get passwordReset => 'تمت إعادة تعيين كلمة المرور.';

  @override
  String get resetPassword => 'إعادة تعيين كلمة المرور';

  @override
  String get resetToken => 'رمز إعادة التعيين من البريد';

  @override
  String get newPassword => 'كلمة المرور الجديدة';

  @override
  String get registrationRequests => 'طلبات إنشاء الحسابات';

  @override
  String get approved => 'تمت الموافقة';

  @override
  String get rejected => 'مرفوض';

  @override
  String get yes => 'نعم';

  @override
  String get no => 'لا';

  @override
  String get approve => 'موافقة';

  @override
  String get reject => 'رفض';

  @override
  String get surveys => 'الاستبيانات';

  @override
  String get editSurvey => 'تعديل الاستبيان';

  @override
  String get createSurvey => 'إنشاء استبيان';

  @override
  String get surveyTitle => 'عنوان الاستبيان';

  @override
  String get surveyDescription => 'وصف الاستبيان';

  @override
  String get addQuestion => 'إضافة سؤال';

  @override
  String get questionText => 'نص السؤال';

  @override
  String get questionType => 'نوع السؤال';

  @override
  String get shortAnswer => 'إجابة قصيرة';

  @override
  String get longAnswer => 'إجابة طويلة';

  @override
  String get singleChoice => 'اختيار واحد';

  @override
  String get multipleChoice => 'اختيار متعدد';

  @override
  String get dropdown => 'قائمة منسدلة';

  @override
  String get yesNo => 'نعم / لا';

  @override
  String get requiredQuestion => 'إجابة مطلوبة';

  @override
  String get optionsCommaSeparated => 'الخيارات، مفصولة بفواصل';

  @override
  String get publishSurvey => 'نشر الاستبيان';

  @override
  String get draft => 'مسودة';

  @override
  String get published => 'منشور';

  @override
  String get submitSurvey => 'إرسال الإجابات';

  @override
  String get surveySubmitted => 'تم إرسال الاستبيان بنجاح.';

  @override
  String get surveyResults => 'نتائج الاستبيان';

  @override
  String get participants => 'عدد المشاركين';

  @override
  String get alreadySubmitted => 'لقد أرسلت إجابتك عن هذا الاستبيان بالفعل.';

  @override
  String get noSurveys => 'لا توجد استبيانات متاحة.';

  @override
  String get signIn => 'تسجيل الدخول';

  @override
  String get alreadyHaveAccount => 'لديك حساب بالفعل؟ سجّل الدخول';

  @override
  String get newAccountPrompt => 'مستخدم جديد؟ اطلب إنشاء حساب';

  @override
  String get myTasks => 'مهامي';

  @override
  String get allTasks => 'جميع المهام';

  @override
  String get manager => 'مدير';

  @override
  String get worker => 'عامل';

  @override
  String get manageUsers => 'إدارة المستخدمين';

  @override
  String get users => 'المستخدمون';

  @override
  String get addUser => 'إضافة مستخدم';

  @override
  String get editUser => 'تعديل المستخدم';

  @override
  String get userRole => 'الدور';

  @override
  String get assignUser => 'إسناد إلى مستخدم';

  @override
  String get saveUser => 'حفظ المستخدم';

  @override
  String get newPasswordOptional => 'كلمة مرور جديدة (اختيارية)';

  @override
  String get userCreated => 'تم إنشاء المستخدم.';

  @override
  String get userUpdated => 'تم تعديل المستخدم.';

  @override
  String get userFieldsRequired => 'الاسم والبريد الإلكتروني مطلوبان.';

  @override
  String get userPasswordRequired => 'كلمة المرور مطلوبة وبحد أدنى 8 أحرف.';

  @override
  String get noUsers => 'لا يوجد مستخدمون.';

  @override
  String get invalidUserListResponse => 'أعاد الخادم قائمة مستخدمين غير صالحة.';

  @override
  String get invalidSurveyListResponse =>
      'أعاد الخادم قائمة استبيانات غير صالحة.';

  @override
  String get signOut => 'تسجيل الخروج';

  @override
  String get signOutQuestion => 'هل تريد تسجيل الخروج؟';

  @override
  String get signOutMessage => 'ستبقى مهامك محفوظة في حسابك.';

  @override
  String get cancel => 'إلغاء';

  @override
  String get newTask => 'مهمة جديدة';

  @override
  String helloUser(String name) {
    return 'مرحبًا، $name';
  }

  @override
  String get deleteTaskQuestion => 'حذف المهمة؟';

  @override
  String deleteTaskConfirmation(String title) {
    return 'سيتم حذف «$title» نهائيًا.';
  }

  @override
  String get delete => 'حذف';

  @override
  String get taskActions => 'إجراءات المهمة';

  @override
  String get edit => 'تعديل';

  @override
  String get editTask => 'تعديل المهمة';

  @override
  String get title => 'العنوان';

  @override
  String get titleRequired => 'عنوان المهمة مطلوب.';

  @override
  String get description => 'الوصف';

  @override
  String get optionalDescription => 'الوصف (اختياري)';

  @override
  String get save => 'حفظ';

  @override
  String get completed => 'مكتملة';

  @override
  String get pending => 'قيد الإنجاز';

  @override
  String get markComplete => 'تحديد كمكتملة';

  @override
  String get markPending => 'إعادة إلى قيد الإنجاز';

  @override
  String get noTasks => 'لا توجد مهام';

  @override
  String get noTasksHint => 'أضف مهمة عندما ترغب في تذكّر شيء.';

  @override
  String get loading => 'جارٍ التحميل...';

  @override
  String get retry => 'إعادة المحاولة';

  @override
  String get taskSaveFailed => 'تعذر حفظ المهمة.';

  @override
  String get sessionExpired => 'انتهت صلاحية الجلسة. سجّل الدخول مجددًا.';

  @override
  String requestFailed(int statusCode) {
    return 'فشل الطلب (HTTP $statusCode).';
  }

  @override
  String get networkError => 'تعذر الاتصال بالخادم. تحقق من الشبكة وعنوان API.';

  @override
  String get unreadableResponse => 'تعذر قراءة استجابة الخادم.';

  @override
  String get invalidServerResponse => 'أعاد الخادم استجابة غير صالحة.';

  @override
  String get invalidUserResponse => 'أعاد الخادم بيانات مستخدم غير صالحة.';

  @override
  String get invalidTaskListResponse => 'أعاد الخادم قائمة مهام غير صالحة.';

  @override
  String get invalidSignInResponse =>
      'أعاد الخادم استجابة تسجيل دخول غير صالحة.';

  @override
  String get unexpectedError => 'حدث خطأ غير متوقع. يرجى المحاولة مجددًا.';

  @override
  String get permissionDenied => 'لا تملك صلاحية تنفيذ هذا الإجراء.';

  @override
  String get taskNotFound => 'المهمة غير موجودة أو لم تعد متاحة.';

  @override
  String get localSessionClearFailed =>
      'حدث خطأ أثناء حذف بيانات الجلسة المحلية.';
}
