# Feature Specification: Mobile-Responsive Invoices View

**Feature Branch**: `feature/mobile-invoices`

**Created**: 2026-06-28

**Status**: Draft

**Input**: User description: "تحسين عرض الفواتير عند استخدام الهاتف — بدل الجدول الصغير، عرض كروت"

## User Scenarios & Testing

### User Story 1 - عرض الفواتير كبطاقات على الموبايل (Priority: P1)

كمدير (Kira)، بدي أشوف فواتير العملاء على التلفون بشكل مريح — كل فاتورة تظهر كبطاقة (card) فيها: رقم الفاتورة، التاريخ، المبلغ، والحالة. أقدّر أدفع أو أطبع من نفس البطاقة.

**Why this priority**: P1 لأنها المشكلة الأساسية اللي بنحسها يومياً — الجدول ما بينضغط على شاشة صغيرة.

**Independent Test**: يمكن اختبارها بفتح صفحة الفواتير على متصفح تلفون (Chrome DevTools mobile view) والتأكد أن البطاقات تظهر بدل الجدول.

**Acceptance Scenarios**:

1. **Given** أنا عم بتصفح عالتلفون، **When** أفتح صفحة فواتير أي عميل، **Then** الفواتير تظهر كبطاقات (cards) مش كجدول
2. **Given** البطاقات ظاهرة، **When** أشوف الفاتورة، **Then** يظهر رقم الفاتورة، التاريخ، المبلغ، والحالة بشكل واضح
3. **Given** في فواتير متعددة، **When** أمرّ على البطاقات، **Then** أقدر أمرّ بسرعة بينهم بدون ما يتكدس النص

---

### User Story 2 - أزرار دفع وطباعة سريعة (Priority: P2)

بدي من كل بطاقة اقدر أدفع الفاتورة أو أطبعها بدون ما أفتح صفحة ثانية.

**Why this priority**: P2 — مهم بس ممكن نضيفه بعد ما تشتغل البطاقات الأساسية.

**Independent Test**: الضغط على زر الدفع يفتح modal الدفع الموجود حالياً.

**Acceptance Scenarios**:

1. **Given** أنا شايف بطاقة الفاتورة، **When** أضغط على "دفع"، **Then** يفتح modal الدفع ويقدر يكمل
2. **Given** أنا شايف بطاقة الفاتورة، **When** أضغط على "طباعة"، **Then** تفتح صفحة الطباعة

---

### User Story 3 - الجدول يضل عالشاشات الكبيرة (Priority: P3)

على اللابتوب أو Desktop، يضل الجدول العادي (DataTable) لأنه أفضل للشاشات الكبيرة.

**Why this priority**: P3 — أكيد بدنا نحافظ على التجربة الحالية للـ Desktop.

**Independent Test**: فتح الصفحة على شاشة 1920px أو أكبر — الجدول يظهر طبيعي.

**Acceptance Scenarios**:

1. **Given** أنا عالـ Desktop (>768px)، **When** أفتح صفحة الفواتير، **Then** يظهر الجدول العادي (DataTable) بدون تغيير
2. **Given** أنا عالـ Desktop، **When** أضغط على زر الدفع/طباعة، **Then** يشتغلون زي ما هما

---

### Edge Cases

- ماذا يحصل عندما يكون في أكثر من 20 فاتورة؟ البطاقات تتقسم بصفحات (pagination) مثل الجدول
- ماذا يحصل عندما يكون المبلغ 0.00؟ يظهر كـ "0.00" عادي
- ماذا يحصل عندما تكون الفاتورة مدفوعة؟ البطاقة يكون لونها أخضر فاتح أو شارة "مدفوع"

## Requirements

### Functional Requirements

- **FR-001**: System MUST display invoices as responsive cards on screens <768px
- **FR-002**: System MUST display invoices as DataTable on screens >=768px
- **FR-003**: Each card MUST show: invoice number, date, amount, status
- **FR-004**: Cards MUST include pay button (opens existing payment modal)
- **FR-005**: Cards MUST include print button (opens print view)
- **FR-006**: Pagination MUST work the same as DataTable pagination
- **FR-007**: Sorting by date/amount MUST work in card view (via DataTable backend)

### Key Entities

- **Invoice**: رقم الفاتورة، التاريخ، المبلغ، الحالة (مدفوع/غير مدفوع/جزئي)

## Success Criteria

- **SC-001**: Mobile screen shows cards instead of table — no horizontal scroll
- **SC-002**: All existing DataTable features (search, pagination, sort) still work on desktop
- **SC-003**: Pay and print buttons function identically to current implementation
- **SC-004**: Zero changes to invoice backend logic (controllers, models, services)

## Assumptions

- Responsive behavior via CSS media queries + Bootstrap's responsive utilities
- Card layout wraps existing DataTable rows — same data, different presentation
- Existing payment modal and print view reused as-is
- No changes to PHP controllers or database queries