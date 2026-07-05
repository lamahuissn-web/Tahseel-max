# Specification: WhatsApp Receipt — Add Collector Name & Payment Time

**Feature Branch**: 

**Created**: 2026-07-05

**Status**: Draft

**Input**: Kira request: نسينا شي مهم ولي هوي إضافة من الشخص الذي عمل قبض والوقت من التاريخ

## Problem

Current WhatsApp receipt message shows:
- ✅ الاشتراك المسدد, المبلغ المدفوع, تاريخ الدفع (date only)
- ❌ Missing: who collected the payment (employee name)
- ❌ Missing: time of payment (clock time)

## Solution

Add 2 new lines to the receipt message:
- 
- 

## Data Sources (Already Exist — No Migration)

| Field | Source | How to Get |
|-------|--------|------------|
| Collector Name |  | Revenue model  |
| Payment Time |  | Format as  |

## Files to Modify

- 

## Updated Message



## Acceptance Criteria
- AC1: Receipt includes الموظف القابض with correct employee name
- AC2: Receipt includes وقت الدفع with correct date and time
- AC3: If Revenue not found, skip collector line gracefully
- AC4: If collector name unresolved, fallback to النظام
- AC5: No migration needed
- AC6: Payment still succeeds if WhatsApp fails (non-blocking)
