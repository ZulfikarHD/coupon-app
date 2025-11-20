import { type VariantProps, cva } from 'class-variance-authority'

export { default as Accordion } from './accordion.vue'
export { default as AccordionContent } from './accordion-content.vue'
export { default as AccordionItem } from './accordion-item.vue'
export { default as AccordionTrigger } from './accordion-trigger.vue'
export { default as Alert } from './alert.vue'
export { default as AlertDescription } from './alert-description.vue'
export { default as Badge } from './badge.vue'
export { default as Breadcrumb } from './breadcrumb.vue'
export { default as BreadcrumbItem } from './breadcrumb-item.vue'
export { default as BreadcrumbLink } from './breadcrumb-link.vue'
export { default as BreadcrumbList } from './breadcrumb-list.vue'
export { default as BreadcrumbPage } from './breadcrumb-page.vue'
export { default as BreadcrumbSeparator } from './breadcrumb-separator.vue'
export { default as Button } from './button.vue'
export { default as Card } from './card.vue'
export { default as CardContent } from './card-content.vue'
export { default as CardDescription } from './card-description.vue'
export { default as CardFooter } from './card-footer.vue'
export { default as CardHeader } from './card-header.vue'
export { default as CardTitle } from './card-title.vue'
export { default as Collapsible } from './collapsible.vue'
export { default as CollapsibleContent } from './collapsible-content.vue'
export { default as CollapsibleTrigger } from './collapsible-trigger.vue'
export { default as Dialog } from './dialog.vue'
export { default as DialogContent } from './dialog-content.vue'
export { default as DialogDescription } from './dialog-description.vue'
export { default as DialogFooter } from './dialog-footer.vue'
export { default as DialogHeader } from './dialog-header.vue'
export { default as DialogTitle } from './dialog-title.vue'
export { default as DropdownMenu } from './dropdown-menu.vue'
export { default as DropdownMenuContent } from './dropdown-menu-content.vue'
export { default as DropdownMenuItem } from './dropdown-menu-item.vue'
export { default as DropdownMenuLabel } from './dropdown-menu-label.vue'
export { default as DropdownMenuSeparator } from './dropdown-menu-separator.vue'
export { default as DropdownMenuTrigger } from './dropdown-menu-trigger.vue'
export { default as Input } from './input.vue'
export { default as Label } from './label.vue'
export { default as Popover } from './popover.vue'
export { default as PopoverContent } from './popover-content.vue'
export { default as PopoverTrigger } from './popover-trigger.vue'
export { default as Select } from './select.vue'
export { default as SelectContent } from './select-content.vue'
export { default as SelectGroup } from './select-group.vue'
export { default as SelectItem } from './select-item.vue'
export { default as SelectTrigger } from './select-trigger.vue'
export { default as SelectValue } from './select-value.vue'
export { default as Separator } from './separator.vue'
export { default as Sheet } from './sheet.vue'
export { default as SheetContent } from './sheet-content.vue'
export { default as SheetDescription } from './sheet-description.vue'
export { default as SheetHeader } from './sheet-header.vue'
export { default as SheetTitle } from './sheet-title.vue'
export { default as SheetTrigger } from './sheet-trigger.vue'
export { default as Tabs } from './tabs.vue'
export { default as TabsContent } from './tabs-content.vue'
export { default as TabsList } from './tabs-list.vue'
export { default as TabsTrigger } from './tabs-trigger.vue'
export { default as Textarea } from './textarea.vue'

export const alertVariants = cva(
  'relative w-full rounded-lg border px-4 py-3 text-sm [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground [&>svg~*]:pl-7',
  {
    variants: {
      variant: {
        default: 'bg-background text-foreground',
        destructive:
          'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)

export type AlertVariants = VariantProps<typeof alertVariants>
