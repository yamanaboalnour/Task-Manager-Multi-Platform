import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_ar.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'generated/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
    : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
        delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[Locale('ar')];

  /// No description provided for @appTitle.
  ///
  /// In ar, this message translates to:
  /// **'إدارة المهام'**
  String get appTitle;

  /// No description provided for @loginTitle.
  ///
  /// In ar, this message translates to:
  /// **'تسجيل الدخول'**
  String get loginTitle;

  /// No description provided for @registerTitle.
  ///
  /// In ar, this message translates to:
  /// **'طلب إنشاء حساب'**
  String get registerTitle;

  /// No description provided for @welcomeBack.
  ///
  /// In ar, this message translates to:
  /// **'مرحبًا بعودتك'**
  String get welcomeBack;

  /// No description provided for @loginSubtitle.
  ///
  /// In ar, this message translates to:
  /// **'سجّل الدخول لمتابعة مهامك.'**
  String get loginSubtitle;

  /// No description provided for @registerSubtitle.
  ///
  /// In ar, this message translates to:
  /// **'يراجع المدير طلبك قبل تفعيل الحساب.'**
  String get registerSubtitle;

  /// No description provided for @apiBaseUrl.
  ///
  /// In ar, this message translates to:
  /// **'عنوان API الأساسي'**
  String get apiBaseUrl;

  /// No description provided for @apiBaseUrlHint.
  ///
  /// In ar, this message translates to:
  /// **'https://your-server.example'**
  String get apiBaseUrlHint;

  /// No description provided for @apiBaseUrlHelp.
  ///
  /// In ar, this message translates to:
  /// **'عنوان المحاكي على Android: http://10.0.2.2:8000'**
  String get apiBaseUrlHelp;

  /// No description provided for @invalidApiUrl.
  ///
  /// In ar, this message translates to:
  /// **'أدخل عنوانًا صالحًا يبدأ بـ http:// أو https://'**
  String get invalidApiUrl;

  /// No description provided for @name.
  ///
  /// In ar, this message translates to:
  /// **'الاسم'**
  String get name;

  /// No description provided for @firstName.
  ///
  /// In ar, this message translates to:
  /// **'الاسم الأول'**
  String get firstName;

  /// No description provided for @lastName.
  ///
  /// In ar, this message translates to:
  /// **'اسم العائلة'**
  String get lastName;

  /// No description provided for @nameRequired.
  ///
  /// In ar, this message translates to:
  /// **'الاسم مطلوب.'**
  String get nameRequired;

  /// No description provided for @email.
  ///
  /// In ar, this message translates to:
  /// **'البريد الإلكتروني'**
  String get email;

  /// No description provided for @emailRequired.
  ///
  /// In ar, this message translates to:
  /// **'أدخل عنوان بريد إلكتروني صالحًا.'**
  String get emailRequired;

  /// No description provided for @password.
  ///
  /// In ar, this message translates to:
  /// **'كلمة المرور'**
  String get password;

  /// No description provided for @passwordRequired.
  ///
  /// In ar, this message translates to:
  /// **'كلمة المرور مطلوبة.'**
  String get passwordRequired;

  /// No description provided for @passwordMinLength.
  ///
  /// In ar, this message translates to:
  /// **'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.'**
  String get passwordMinLength;

  /// No description provided for @confirmPassword.
  ///
  /// In ar, this message translates to:
  /// **'تأكيد كلمة المرور'**
  String get confirmPassword;

  /// No description provided for @passwordsMismatch.
  ///
  /// In ar, this message translates to:
  /// **'كلمتا المرور غير متطابقتين.'**
  String get passwordsMismatch;

  /// No description provided for @createAccount.
  ///
  /// In ar, this message translates to:
  /// **'إرسال طلب إنشاء الحساب'**
  String get createAccount;

  /// No description provided for @requestSubmitted.
  ///
  /// In ar, this message translates to:
  /// **'تم إرسال طلب إنشاء الحساب للمراجعة.'**
  String get requestSubmitted;

  /// No description provided for @forgotPassword.
  ///
  /// In ar, this message translates to:
  /// **'نسيت كلمة المرور؟'**
  String get forgotPassword;

  /// No description provided for @forgotPasswordInstructions.
  ///
  /// In ar, this message translates to:
  /// **'أدخل بريدك الإلكتروني لإرسال رابط آمن لإعادة التعيين.'**
  String get forgotPasswordInstructions;

  /// No description provided for @resetLinkSent.
  ///
  /// In ar, this message translates to:
  /// **'إذا كان البريد مرتبطًا بحساب، فسيتم إرسال رابط إعادة التعيين.'**
  String get resetLinkSent;

  /// No description provided for @sendResetLink.
  ///
  /// In ar, this message translates to:
  /// **'إرسال رابط إعادة التعيين'**
  String get sendResetLink;

  /// No description provided for @passwordReset.
  ///
  /// In ar, this message translates to:
  /// **'تمت إعادة تعيين كلمة المرور.'**
  String get passwordReset;

  /// No description provided for @resetPassword.
  ///
  /// In ar, this message translates to:
  /// **'إعادة تعيين كلمة المرور'**
  String get resetPassword;

  /// No description provided for @resetToken.
  ///
  /// In ar, this message translates to:
  /// **'رمز إعادة التعيين من البريد'**
  String get resetToken;

  /// No description provided for @newPassword.
  ///
  /// In ar, this message translates to:
  /// **'كلمة المرور الجديدة'**
  String get newPassword;

  /// No description provided for @registrationRequests.
  ///
  /// In ar, this message translates to:
  /// **'طلبات إنشاء الحسابات'**
  String get registrationRequests;

  /// No description provided for @approved.
  ///
  /// In ar, this message translates to:
  /// **'تمت الموافقة'**
  String get approved;

  /// No description provided for @rejected.
  ///
  /// In ar, this message translates to:
  /// **'مرفوض'**
  String get rejected;

  /// No description provided for @yes.
  ///
  /// In ar, this message translates to:
  /// **'نعم'**
  String get yes;

  /// No description provided for @no.
  ///
  /// In ar, this message translates to:
  /// **'لا'**
  String get no;

  /// No description provided for @approve.
  ///
  /// In ar, this message translates to:
  /// **'موافقة'**
  String get approve;

  /// No description provided for @reject.
  ///
  /// In ar, this message translates to:
  /// **'رفض'**
  String get reject;

  /// No description provided for @surveys.
  ///
  /// In ar, this message translates to:
  /// **'الاستبيانات'**
  String get surveys;

  /// No description provided for @editSurvey.
  ///
  /// In ar, this message translates to:
  /// **'تعديل الاستبيان'**
  String get editSurvey;

  /// No description provided for @createSurvey.
  ///
  /// In ar, this message translates to:
  /// **'إنشاء استبيان'**
  String get createSurvey;

  /// No description provided for @surveyTitle.
  ///
  /// In ar, this message translates to:
  /// **'عنوان الاستبيان'**
  String get surveyTitle;

  /// No description provided for @surveyDescription.
  ///
  /// In ar, this message translates to:
  /// **'وصف الاستبيان'**
  String get surveyDescription;

  /// No description provided for @addQuestion.
  ///
  /// In ar, this message translates to:
  /// **'إضافة سؤال'**
  String get addQuestion;

  /// No description provided for @questionText.
  ///
  /// In ar, this message translates to:
  /// **'نص السؤال'**
  String get questionText;

  /// No description provided for @questionType.
  ///
  /// In ar, this message translates to:
  /// **'نوع السؤال'**
  String get questionType;

  /// No description provided for @shortAnswer.
  ///
  /// In ar, this message translates to:
  /// **'إجابة قصيرة'**
  String get shortAnswer;

  /// No description provided for @longAnswer.
  ///
  /// In ar, this message translates to:
  /// **'إجابة طويلة'**
  String get longAnswer;

  /// No description provided for @singleChoice.
  ///
  /// In ar, this message translates to:
  /// **'اختيار واحد'**
  String get singleChoice;

  /// No description provided for @multipleChoice.
  ///
  /// In ar, this message translates to:
  /// **'اختيار متعدد'**
  String get multipleChoice;

  /// No description provided for @dropdown.
  ///
  /// In ar, this message translates to:
  /// **'قائمة منسدلة'**
  String get dropdown;

  /// No description provided for @yesNo.
  ///
  /// In ar, this message translates to:
  /// **'نعم / لا'**
  String get yesNo;

  /// No description provided for @requiredQuestion.
  ///
  /// In ar, this message translates to:
  /// **'إجابة مطلوبة'**
  String get requiredQuestion;

  /// No description provided for @optionsCommaSeparated.
  ///
  /// In ar, this message translates to:
  /// **'الخيارات، مفصولة بفواصل'**
  String get optionsCommaSeparated;

  /// No description provided for @publishSurvey.
  ///
  /// In ar, this message translates to:
  /// **'نشر الاستبيان'**
  String get publishSurvey;

  /// No description provided for @draft.
  ///
  /// In ar, this message translates to:
  /// **'مسودة'**
  String get draft;

  /// No description provided for @published.
  ///
  /// In ar, this message translates to:
  /// **'منشور'**
  String get published;

  /// No description provided for @submitSurvey.
  ///
  /// In ar, this message translates to:
  /// **'إرسال الإجابات'**
  String get submitSurvey;

  /// No description provided for @surveySubmitted.
  ///
  /// In ar, this message translates to:
  /// **'تم إرسال الاستبيان بنجاح.'**
  String get surveySubmitted;

  /// No description provided for @surveyResults.
  ///
  /// In ar, this message translates to:
  /// **'نتائج الاستبيان'**
  String get surveyResults;

  /// No description provided for @participants.
  ///
  /// In ar, this message translates to:
  /// **'عدد المشاركين'**
  String get participants;

  /// No description provided for @alreadySubmitted.
  ///
  /// In ar, this message translates to:
  /// **'لقد أرسلت إجابتك عن هذا الاستبيان بالفعل.'**
  String get alreadySubmitted;

  /// No description provided for @noSurveys.
  ///
  /// In ar, this message translates to:
  /// **'لا توجد استبيانات متاحة.'**
  String get noSurveys;

  /// No description provided for @signIn.
  ///
  /// In ar, this message translates to:
  /// **'تسجيل الدخول'**
  String get signIn;

  /// No description provided for @alreadyHaveAccount.
  ///
  /// In ar, this message translates to:
  /// **'لديك حساب بالفعل؟ سجّل الدخول'**
  String get alreadyHaveAccount;

  /// No description provided for @newAccountPrompt.
  ///
  /// In ar, this message translates to:
  /// **'مستخدم جديد؟ اطلب إنشاء حساب'**
  String get newAccountPrompt;

  /// No description provided for @myTasks.
  ///
  /// In ar, this message translates to:
  /// **'مهامي'**
  String get myTasks;

  /// No description provided for @allTasks.
  ///
  /// In ar, this message translates to:
  /// **'جميع المهام'**
  String get allTasks;

  /// No description provided for @manager.
  ///
  /// In ar, this message translates to:
  /// **'مدير'**
  String get manager;

  /// No description provided for @worker.
  ///
  /// In ar, this message translates to:
  /// **'عامل'**
  String get worker;

  /// No description provided for @manageUsers.
  ///
  /// In ar, this message translates to:
  /// **'إدارة المستخدمين'**
  String get manageUsers;

  /// No description provided for @users.
  ///
  /// In ar, this message translates to:
  /// **'المستخدمون'**
  String get users;

  /// No description provided for @addUser.
  ///
  /// In ar, this message translates to:
  /// **'إضافة مستخدم'**
  String get addUser;

  /// No description provided for @editUser.
  ///
  /// In ar, this message translates to:
  /// **'تعديل المستخدم'**
  String get editUser;

  /// No description provided for @userRole.
  ///
  /// In ar, this message translates to:
  /// **'الدور'**
  String get userRole;

  /// No description provided for @assignUser.
  ///
  /// In ar, this message translates to:
  /// **'إسناد إلى مستخدم'**
  String get assignUser;

  /// No description provided for @saveUser.
  ///
  /// In ar, this message translates to:
  /// **'حفظ المستخدم'**
  String get saveUser;

  /// No description provided for @newPasswordOptional.
  ///
  /// In ar, this message translates to:
  /// **'كلمة مرور جديدة (اختيارية)'**
  String get newPasswordOptional;

  /// No description provided for @userCreated.
  ///
  /// In ar, this message translates to:
  /// **'تم إنشاء المستخدم.'**
  String get userCreated;

  /// No description provided for @userUpdated.
  ///
  /// In ar, this message translates to:
  /// **'تم تعديل المستخدم.'**
  String get userUpdated;

  /// No description provided for @userFieldsRequired.
  ///
  /// In ar, this message translates to:
  /// **'الاسم والبريد الإلكتروني مطلوبان.'**
  String get userFieldsRequired;

  /// No description provided for @userPasswordRequired.
  ///
  /// In ar, this message translates to:
  /// **'كلمة المرور مطلوبة وبحد أدنى 8 أحرف.'**
  String get userPasswordRequired;

  /// No description provided for @noUsers.
  ///
  /// In ar, this message translates to:
  /// **'لا يوجد مستخدمون.'**
  String get noUsers;

  /// No description provided for @invalidUserListResponse.
  ///
  /// In ar, this message translates to:
  /// **'أعاد الخادم قائمة مستخدمين غير صالحة.'**
  String get invalidUserListResponse;

  /// No description provided for @invalidSurveyListResponse.
  ///
  /// In ar, this message translates to:
  /// **'أعاد الخادم قائمة استبيانات غير صالحة.'**
  String get invalidSurveyListResponse;

  /// No description provided for @signOut.
  ///
  /// In ar, this message translates to:
  /// **'تسجيل الخروج'**
  String get signOut;

  /// No description provided for @signOutQuestion.
  ///
  /// In ar, this message translates to:
  /// **'هل تريد تسجيل الخروج؟'**
  String get signOutQuestion;

  /// No description provided for @signOutMessage.
  ///
  /// In ar, this message translates to:
  /// **'ستبقى مهامك محفوظة في حسابك.'**
  String get signOutMessage;

  /// No description provided for @cancel.
  ///
  /// In ar, this message translates to:
  /// **'إلغاء'**
  String get cancel;

  /// No description provided for @newTask.
  ///
  /// In ar, this message translates to:
  /// **'مهمة جديدة'**
  String get newTask;

  /// No description provided for @helloUser.
  ///
  /// In ar, this message translates to:
  /// **'مرحبًا، {name}'**
  String helloUser(String name);

  /// No description provided for @deleteTaskQuestion.
  ///
  /// In ar, this message translates to:
  /// **'حذف المهمة؟'**
  String get deleteTaskQuestion;

  /// No description provided for @deleteTaskConfirmation.
  ///
  /// In ar, this message translates to:
  /// **'سيتم حذف «{title}» نهائيًا.'**
  String deleteTaskConfirmation(String title);

  /// No description provided for @delete.
  ///
  /// In ar, this message translates to:
  /// **'حذف'**
  String get delete;

  /// No description provided for @taskActions.
  ///
  /// In ar, this message translates to:
  /// **'إجراءات المهمة'**
  String get taskActions;

  /// No description provided for @edit.
  ///
  /// In ar, this message translates to:
  /// **'تعديل'**
  String get edit;

  /// No description provided for @editTask.
  ///
  /// In ar, this message translates to:
  /// **'تعديل المهمة'**
  String get editTask;

  /// No description provided for @title.
  ///
  /// In ar, this message translates to:
  /// **'العنوان'**
  String get title;

  /// No description provided for @titleRequired.
  ///
  /// In ar, this message translates to:
  /// **'عنوان المهمة مطلوب.'**
  String get titleRequired;

  /// No description provided for @description.
  ///
  /// In ar, this message translates to:
  /// **'الوصف'**
  String get description;

  /// No description provided for @optionalDescription.
  ///
  /// In ar, this message translates to:
  /// **'الوصف (اختياري)'**
  String get optionalDescription;

  /// No description provided for @save.
  ///
  /// In ar, this message translates to:
  /// **'حفظ'**
  String get save;

  /// No description provided for @completed.
  ///
  /// In ar, this message translates to:
  /// **'مكتملة'**
  String get completed;

  /// No description provided for @pending.
  ///
  /// In ar, this message translates to:
  /// **'قيد الإنجاز'**
  String get pending;

  /// No description provided for @markComplete.
  ///
  /// In ar, this message translates to:
  /// **'تحديد كمكتملة'**
  String get markComplete;

  /// No description provided for @markPending.
  ///
  /// In ar, this message translates to:
  /// **'إعادة إلى قيد الإنجاز'**
  String get markPending;

  /// No description provided for @noTasks.
  ///
  /// In ar, this message translates to:
  /// **'لا توجد مهام'**
  String get noTasks;

  /// No description provided for @noTasksHint.
  ///
  /// In ar, this message translates to:
  /// **'أضف مهمة عندما ترغب في تذكّر شيء.'**
  String get noTasksHint;

  /// No description provided for @loading.
  ///
  /// In ar, this message translates to:
  /// **'جارٍ التحميل...'**
  String get loading;

  /// No description provided for @retry.
  ///
  /// In ar, this message translates to:
  /// **'إعادة المحاولة'**
  String get retry;

  /// No description provided for @taskSaveFailed.
  ///
  /// In ar, this message translates to:
  /// **'تعذر حفظ المهمة.'**
  String get taskSaveFailed;

  /// No description provided for @sessionExpired.
  ///
  /// In ar, this message translates to:
  /// **'انتهت صلاحية الجلسة. سجّل الدخول مجددًا.'**
  String get sessionExpired;

  /// No description provided for @requestFailed.
  ///
  /// In ar, this message translates to:
  /// **'فشل الطلب (HTTP {statusCode}).'**
  String requestFailed(int statusCode);

  /// No description provided for @networkError.
  ///
  /// In ar, this message translates to:
  /// **'تعذر الاتصال بالخادم. تحقق من الشبكة وعنوان API.'**
  String get networkError;

  /// No description provided for @unreadableResponse.
  ///
  /// In ar, this message translates to:
  /// **'تعذر قراءة استجابة الخادم.'**
  String get unreadableResponse;

  /// No description provided for @invalidServerResponse.
  ///
  /// In ar, this message translates to:
  /// **'أعاد الخادم استجابة غير صالحة.'**
  String get invalidServerResponse;

  /// No description provided for @invalidUserResponse.
  ///
  /// In ar, this message translates to:
  /// **'أعاد الخادم بيانات مستخدم غير صالحة.'**
  String get invalidUserResponse;

  /// No description provided for @invalidTaskListResponse.
  ///
  /// In ar, this message translates to:
  /// **'أعاد الخادم قائمة مهام غير صالحة.'**
  String get invalidTaskListResponse;

  /// No description provided for @invalidSignInResponse.
  ///
  /// In ar, this message translates to:
  /// **'أعاد الخادم استجابة تسجيل دخول غير صالحة.'**
  String get invalidSignInResponse;

  /// No description provided for @unexpectedError.
  ///
  /// In ar, this message translates to:
  /// **'حدث خطأ غير متوقع. يرجى المحاولة مجددًا.'**
  String get unexpectedError;

  /// No description provided for @permissionDenied.
  ///
  /// In ar, this message translates to:
  /// **'لا تملك صلاحية تنفيذ هذا الإجراء.'**
  String get permissionDenied;

  /// No description provided for @taskNotFound.
  ///
  /// In ar, this message translates to:
  /// **'المهمة غير موجودة أو لم تعد متاحة.'**
  String get taskNotFound;

  /// No description provided for @localSessionClearFailed.
  ///
  /// In ar, this message translates to:
  /// **'حدث خطأ أثناء حذف بيانات الجلسة المحلية.'**
  String get localSessionClearFailed;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['ar'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'ar':
      return AppLocalizationsAr();
  }

  throw FlutterError(
    'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
    'an issue with the localizations generation tool. Please file an issue '
    'on GitHub with a reproducible sample app and the gen-l10n configuration '
    'that was used.',
  );
}
