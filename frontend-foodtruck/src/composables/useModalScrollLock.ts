import { watch, onBeforeUnmount, type Ref } from 'vue'

export function useModalScrollLock(isModalOpen: Ref<boolean>) {

    watch(
        isModalOpen,
        (isOpen) => {
            document.documentElement.classList.toggle(
                'modal-open',
                isOpen
            )

            document.body.classList.toggle(
                'modal-open',
                isOpen
            )
        },
        { immediate: true }
    )

    onBeforeUnmount(() => {
        document.documentElement.classList.remove('modal-open')
        document.body.classList.remove('modal-open')
    })
}