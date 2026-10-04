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
  String get registerTitle => 'إنشاء حساب';

  @override
  String get welcomeBack => 'مرحبًا بعودتك';

  @override
  String get loginSubtitle => 'سجّل الدخول لمتابعة مهامك.';

  @override
  String get registerSubtitle => 'أنشئ حسابًا لمزامنة مهامك بين أجهزتك.';

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
  String get createAccount => 'إنشاء حساب';

  @override
  String get signIn => 'تسجيل الدخول';

  @override
  String get alreadyHaveAccount => 'لديك حساب بالفعل؟ سجّل الدخول';

  @override
  String get newAccountPrompt => 'مستخدم جديد؟ أنشئ حسابًا';

  @override
  String get myTasks => 'مهامي';

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
